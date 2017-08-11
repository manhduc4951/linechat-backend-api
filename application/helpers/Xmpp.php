<?php

require_once THIRDPARTY_PATH . '/jaxl3/jaxl.php';

/**
 * Xmpp Business class
 * 
 * @package LineChatApp
 * @subpackage App_Helper
 * @author duyld
 */
class Xmpp
{
    /**
     * Privacy namespace
     */
    CONST PRIVACY_NAMESPACE = 'jabber:iq:privacy';
    
    /**
     * Default pubsub service id
     */
    const PUBSUB_SERVICE = 'pubsub';
    
    /**
     * Pubsub subscribed state
     */
    const PUBSUB_NODE_SUBSCRIBED = 'subscribed';
    const PUBSUB_NODE_PENDING = 'pending';
    const PUBSUB_NODE_NONE = 'none';
    
    /**
     * Pubsub affiliations
     */
    const PUBSUB_NODE_AFFILIATION_PUBLISHER = 'publisher';
    
    /**
     * Item id for pubsub node wil contains node info
     */
    const PUBSUB_NODE_INFO_ITEM_ID = 'node_info';
    
    /**
     * Item id for pubsub node wil contains number of subscribed members
     */
    const PUBSUB_NODE_MEMBER_ITEM_ID = 'node_member';
    
    /**
     * Xmpp Pub-sub node access model
     */
    const PUBSUB_NODE_ACCESS_MODEL_OPEN = 'open';
    const PUBSUB_NODE_ACCESS_MODEL_AUTHORIZE = 'authorize';
    
    /**
     * Multi-user chat affiliations
     */
    const MUC_AFFILIATION_OWNER = '10';
    
    /**
     * Error connection flag
     */
    const ERROR_CONNECTION = '-1';
    const ERROR_AUTH_FAILURE = '-2';
    const ERROR_UNKNOWN = '-3';
    
    // additional namespace
    const NS_INVITE_GROUP = 'qsoft:smartphone:group-invitation';
    
    /**
     * Current running xmpp instance
     * 
     * @var Xmpp
     */
    static $instance;
    
    /**
     * Jaxl client to connect to jabber server
     * 
     * @var JAXL
     */
    protected $client;
    
    /**
     * @var XmppResponse
     */
    protected $response;
    
    /**
     * Configuration
     * 
     * @var array
     */
    protected $config;
    
    protected $end = false;
    
    /**
     * Construtor
     * 
     * @param   array   $config
     * @param   string  $jid        The bare jid
     * @param   string  $password   Password
     * @return  void
     */
    public function __construct($config = array(), $jid = null, $password = null)
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }
        
        $this->config = $config;
        
        $this->client = new JAXL(array(
        	// credentials
        	'jid'           => $jid,
        	'pass'          => $password,
        	// srv lookup is done if not provided
        	'host'          => $this->config['host'],
        	// result from srv lookup used by default
        	'port'          => $this->config['port'],
            // defaults to false
            'force_tls'     => (boolean) $this->config['force_tls'],
            // defaults to PLAIN if supported, else other methods will be automatically tried
            'auth_type'     => $this->config['auth_type'],
            // log system parameters
            'log_level'     => $this->config['log_level'],
            'log_path'      => $this->config['log_path'],
            // disable exception handles
            // set strict to false if running phpunit testing
            'strict'		=> defined('STDIN') ? false : true,
        ));
        
        $this->_setRequiredCallback();
        
        $this->client->require_xep(array(
        	'0077', // In-Band Registration
            '0060', // Publish-Subscribe
            '0045', // Multi-user chat
            '0199', // Xmpp Ping
        ));
        
        self::$instance = $this;
    }
    
    /**
     * Retrieve Xmpp helper instance
     * 
     * @return  Xmpp
     */
    public static function getInstance()
    {
        return self::$instance;
    }
    
    /**
     * Return current Jaxl client
     * 
     * @return JAXL
     */
    public function getClient()
    {
        return $this->client;
    }
    
    /**
     * Get response
     * 
     * @return  XmppResponse
     */
    public function getResponse()
    {
        return $this->response;
    }
    
    /**
     * Set response
     * 
     * @param   XmppResponse    $response
     * @return  Xmpp
     */
    public function setResponse(XmppResponse $response)
    {
        $this->response = $response;
        
        return $this;
    }
    
    /**
     * Adding required Jaxl client callback method
     * 
     * @return  Xmpp
     */
    protected function _setRequiredCallback()
    {
        // callback on error response
        $this->client->add_cb('on_error_iq', function($stanza) {
            $instance = Xmpp::getInstance();
            if ( ! $instance->getResponse()) {
                if (isset($stanza->childrens[1]->attrs['code'])) {
                    $response = new XmppResponse($stanza->childrens[1]->attrs['code']);
                } else {
                    $response = new XmppResponse(Xmpp::ERROR_UNKNOWN);
                }
                
                $instance->setResponse($response);
            }
            
            $instance->getClient()->send_end_stream();
        });
        
        // callback on connection error
        $this->client->add_cb('on_connect_error', function($stanza) {
            $response = new XmppResponse(Xmpp::ERROR_CONNECTION);
            
            $instance = Xmpp::getInstance();
            $instance->setResponse($response);
            $instance->getClient()->send_end_stream();
        });
        
        // callback on authenticate error
        $this->client->add_cb('on_auth_failure', function($stanza) {
            $response = new XmppResponse(Xmpp::ERROR_AUTH_FAILURE);
            
            $instance = Xmpp::getInstance();
            $instance->setResponse($response);
            $instance->getClient()->send_end_stream();
        });
        
        return $this;
    }
    
    /**
     * Send an instant message to single or multi user
     * 
     * @param   string $message
     * @param   mixed $toJid
     * @return  App_Helper_Xmpp
     */
    public function sendMessage($message, $toJid)
    {
        $this->client->add_cb('on_auth_success', function() use ($toJid, $message) {
        	$client = Xmpp::getInstance()->getClient();
            
            if ( ! is_array($toJid)) {
                $toJid = array($toJid);
            }
            
            foreach ($toJid as $jid) {
                $client->send_chat_msg($jid, $message);
            }
        	
            $client->send_end_stream();
            Xmpp::getInstance()->setResponse(new XmppResponse());
        });
        
        $this->client->start();
        
        return $this->getResponse();
    }
    
    public function register($chatId, $password, $name = '')
    {
    	$data = array(
    		'username' => $chatId,
    		'password' => $password,
    		'name' => $name,
    	);
    	$domain = $this->config['domain'];
    	
    	$this->client->add_cb('on_stream_features', function() use ($domain, $data) {
    		$client = Xmpp::getInstance()->getClient();
    		$client->xeps['0077']->set_form($domain, $data);
    		return array('Xmpp', "wait_for_register_form");
    	});
    	
    	$this->client->start();
    	
    	return $this->getResponse();
    }
    
    public function delete()
    {
        $this->client->add_cb('on_auth_success', function() {
            $client = Xmpp::getInstance()->getClient();
            $client->xeps['0077']->delete();
            $client->send_end_stream();
        });
        
        $this->client->start();
        $this->setResponse(new XmppResponse());
             
        return $this->getResponse();
    }
    
    public static function wait_for_register_form($event, $args)
    {
    	$instance = Xmpp::getInstance();
    	$stanza = $args[0];
    	if($stanza->name == 'iq') {
    		if($stanza->attrs['type'] == 'result') {
    			$instance->setResponse(new XmppResponse());
    			$instance->send_end_stream();
    			return "logged_out";
    		}
    		else if($stanza->attrs['type'] == 'error') {
    			$error = $stanza->exists('error');
    			$response = new XmppResponse($error->attrs['code']);
    			$instance->setResponse($response);
    			$instance->send_end_stream();
    			return "logged_out";
    		}
    	}
    }
    
    /**
     * Invite users to a node
     * 
     * @param   array   $members
     * @param   string  $node
     * @param   string  $service
     * @return void
     */
    public function inviteToNode($members, $node, $info = array(), $service = self::PUBSUB_SERVICE)
    {
        $this->client->add_cb('on_auth_success', function() use ($members, $node, $info, $service) {
        	$instance = Xmpp::getInstance();
            
            if ( ! is_array($members)) {
                $members = array($members);
            }
            
            foreach ($members as $jid) {
                $stanza = $instance->_createInviteToNodeStanza($jid, $node, $info, $service);
                $instance->getClient()->send($stanza);
            }
        	
            $instance->setResponse(new XmppResponse());
            $instance->send_end_stream();
        });
        
        $this->client->start();
        
        return $this->getResponse();
    }
    
    /**
     * Create invite to node message stanza to alert invited user
     * 
     * @param string $jid
     * @param string $node
     * @param array $info
     * @param string $service
     * @return JAXLXml
     */
    public function _createInviteToNodeStanza($jid, $node, array $info = array(), $service = self::PUBSUB_SERVICE)
    {
        $service = $service . '.' . $this->config['domain'];
        $info['node'] = $node;
        $stanza = new JAXLXml('message', 'jabber:client', array('from' => $service, 'to' => $jid, 'type' => 'chat'));
        $stanza
            ->c('invite', self::NS_INVITE_GROUP, $info)->up()
            ->c('body')->t(self::NS_INVITE_GROUP)->up()
        ;
        
        return $stanza;
    }
    
    /**
     * Create new Pubsub node
     * 
     * @param   string  $node
     * @param   string  $service
     * @param   array 	$info
     * @param   array   $configurations
     * @return  XmppResponse
     */
    public function createNode($node = null, $service = self::PUBSUB_SERVICE,
    	$info = array(), $configurations = array())
    {
        $service = $service . '.' . $this->config['domain'];
        
        // send a create node stanza
        $this->client->add_cb('on_auth_success', function() use ($node, $service, $configurations) {
        	$client = Xmpp::getInstance()->getClient();
        	$client->xeps['0060']->create_node($service, $node, $configurations);
        });
        
        // on success response
        $this->client->add_cb('on_result_iq', function($stanza) use ($service, $info) {
            if ($stanza->from == $service) {
                
                // this is response of adding info item to node, just close stream connection
                if (empty($stanza->childrens[0])) {
                    Xmpp::getInstance()->getClient()->send_end_stream();
                    return;
                }
                
                $response = new XmppResponse();
                $response->node_id = $stanza->childrens[0]->childrens[0]->attrs['node'];
                
                $instance = Xmpp::getInstance();
                $instance->setResponse($response);
                
                // continous to insert node info item entry
            	$item = $instance->_createInfoItemXml(Xmpp::PUBSUB_NODE_INFO_ITEM_ID, $info);
                                
                $instance->getClient()->xeps['0060']->publish_item(
                    $service, $response->node_id, $item);
            }
        });
        
        $this->client->start();
        
        return $this->getResponse();
    }
    
    /**
     * Update node info
     * Will update to item that contains node information
     * 
     * @param   string $node
     * @param   array  $info
     * @param   string $service
     * @return  XmppResponse
     */
    public function updateNode($node, $info = array(), $configurations = array(), $service = self::PUBSUB_SERVICE)
    {
        $payload = $this->_createInfoItemXml(Xmpp::PUBSUB_NODE_INFO_ITEM_ID, $info);
        $service = $service . '.' . $this->config['domain'];
        
        // send a update node configure stanza
        $this->client->add_cb('on_auth_success', function() use ($node, $service, $configurations) {
        	$client = Xmpp::getInstance()->getClient();
            $client->step = 1;
            $client->xeps['0060']->configure_node($service, $node, $configurations);
        });
        
        // on success response
        $this->client->add_cb('on_result_iq', function($stanza) use ($node, $service, $payload) {
            $instance = Xmpp::getInstance();
            
            // send a modify node info item stanza
            if ($stanza->from == $service AND $instance->getClient()->step == 1) {
                $instance->getClient()->step = 2;
                $instance->getClient()->xeps['0060']->publish_item($service, $node, $payload);
            }
            
            if ($stanza->from == $service AND $instance->getClient()->step == 2) {
                $instance->getClient()->step = 3;
                $instance->setResponse(new XmppResponse());
                $instance->send_end_stream();
            }
        });
        
        $this->client->start();
        
        return $this->getResponse();
    }
    
    /**
     * Publish an node item
     * 
     * @param   string  $node
     * @param   JAXLXml $payload
     * @param   string  $service
     * @return  XmppResponse
     */
    public function publishItem($node, $payload, $service = self::PUBSUB_SERVICE)
    {
        $service = $service . '.' . $this->config['domain'];
        
        // send a publish item stanza
        $this->client->add_cb('on_auth_success', function() use ($node, $service, $payload) {
        	$client = Xmpp::getInstance()->getClient();
        	$client->xeps['0060']->publish_item($service, $node, $payload);
        });
        
        // on success response
        $this->client->add_cb('on_result_iq', function($stanza) use ($service) {
            if ($stanza->from == $service) {
                $instance = Xmpp::getInstance();
                $instance->setResponse(new XmppResponse());
                $instance->getClient()->send_end_stream();
            }
        });
        
        $this->client->start();
        
        return $this->getResponse();
    }
    
    /**
     * Create xml object of item for contains the node information
     * 
     * @param   string $itemId      If null, the xmpp service will auto generate
     * @param   array  $info
     * @return  JAXLXml
     */
    public function _createInfoItemXml($itemId = null, $info = array())
    {
        $item = new JAXLXml('item', null, array('id' => $itemId));
        $item->c('entry', 'http://www.w3.org/2005/Atom');
        
		foreach ($info as $name => $value) {
			$item->c($name)->t($value)->up();
		}
		
		$gmtime = strtotime(gmdate('Y-m-d H:i:s'));
        $item->c('updated')->t($gmtime)->up();
        
        return $item;
    }
    
     /**
     * Create xml object of item for contains the number of subscribed members
     * 
     * @param   string $total_members
     * @param   string $itemId          If null, the xmpp service will auto generate
     * @return  JAXLXml
     */
    public function _createTotalMembersItemXml($total_members = 1, $itemId = null)
    {
        $item = new JAXLXml('item', null, array('id' => $itemId));
        $gmtime = strtotime(gmdate('Y-m-d H:i:s'));
        $item
            ->c('entry', 'http://www.w3.org/2005/Atom')
            ->c('total_members')->t($total_members)->up()
            ->c('updated')->t($gmtime)->up()
        ;
        
        return $item;
    }
    
    /**
     * Subscribe a node
     * 
     * @param   string $node
     * @param   string $service
     * @return  XmppResponse
     */
    public function subscribeNode($node, $service = self::PUBSUB_SERVICE)
    {
        $service = $service . '.' . $this->config['domain'];
        
        // send a subscribe node stanza
        $this->client->add_cb('on_auth_success', function() use ($node, $service) {
        	$client = Xmpp::getInstance()->getClient();
        	$client->xeps['0060']->subscribe($service, $node, $client->full_jid->bare);
        });
        
        // on success response
        $this->client->add_cb('on_result_iq', function($stanza) use ($service) {
            if ($stanza->from == $service) {
                $instance = Xmpp::getInstance();
                $instance->setResponse(new XmppResponse());
                $instance->getClient()->send_end_stream();
            }
        });
        
        $this->client->start();
        
        return $this->getResponse();
    }
    
    /**
     * Unsubscribe a node
     * 
     * @param   string $node
     * @param   string $service
     * @return  XmppResponse
     */
    public function unsubscribeNode($node, $service = self::PUBSUB_SERVICE)
    {
        $service = $service . '.' . $this->config['domain'];
        
        // send a subscribe node stanza
        $this->client->add_cb('on_auth_success', function() use ($node, $service) {
        	$client = Xmpp::getInstance()->getClient();
        	$client->xeps['0060']->unsubscribe($service, $node, $client->full_jid->bare);
            $client->send_end_stream();
        });
        
        $this->client->start();
        
        return new XmppResponse();
    }
    
    public function updateMember($node, $service = self::PUBSUB_SERVICE)
    {
        $service = $service . '.' . $this->config['domain'];
        
        // send a subscribe node stanza
        $this->client->add_cb('on_auth_success', function() use ($node, $service) {
        	$client = Xmpp::getInstance()->getClient();
        	$client->xeps['0060']->get_suscriber_list($service, $node);
        });
        
        $this->client->add_cb('on_result_iq', function($stanza) use ($service, $node) {
            $instance = Xmpp::getInstance();
            if ($stanza->from == $service AND ! $instance->isEndStream()) {
                $instance->_onUpdateNodeMember($stanza, $node, $service);
                $instance->send_end_stream();
            }
        });
        
        $this->client->start();
        
        return new XmppResponse();
    }
    
    /**
     * Multiple affiliations modifications for owner
     * 
     * @param   string          $node
     * @param   string|array    $users
     * @param   string          $service
     * @return  XmppResponse
     */
    public function setNodeAffiliation($node, $users, $affliation, $service = self::PUBSUB_SERVICE)
    {
        $service = $service . '.' . $this->config['domain'];
        
        $this->client->add_cb('on_auth_success', function() use ($node, $service, $users, $affliation) {
        	$client = Xmpp::getInstance()->getClient();
            $client->step = 1;
        	$client->xeps['0060']->set_affiliations($service, $node, $users, $affliation);
        });
        
        // on success response
        $this->client->add_cb('on_result_iq', function($stanza) use ($service, $node) {
            if ($stanza->from == $service) {
                $instance = Xmpp::getInstance();
                if ($instance->getClient()->step == 1) {
                    $instance->setResponse(new XmppResponse());
                
                    // update the member list
                    $instance->getClient()->step = 2;
                    $instance->getClient()->xeps['0060']->get_suscriber_list($service, $node);
                } elseif ($instance->getClient()->step == 2) {
                    $instance->getClient()->step = 3;
                    $instance->_onUpdateNodeMember($stanza, $node, $service);
                    $instance->getClient()->send_end_stream();
                }
                
            }
        });
        
        $this->client->start();
        
        return $this->getResponse();
    }

    public function setNodeSubscriptionState($node, $users,
        $state = self::PUBSUB_NODE_SUBSCRIBED, $service = self::PUBSUB_SERVICE)
    {
        $service = $service . '.' . $this->config['domain'];
        
        // convert members array to expected format
        if ( ! is_array($users)) {
            $users = array($users => $state);
        }
        
        foreach ($users as $index => $user) {
            if (is_int($index)) {
                unset($users[$index]);
                $users[$user] = $state;
            }
        }
        
        $this->client->add_cb('on_auth_success', function() use ($node, $service, $users) {
            $client = Xmpp::getInstance()->getClient();
            $client->xeps['0060']->set_subscriptions_state($service, $node, $users);
        });
        
        // on success response
        $this->client->add_cb('on_result_iq', function($stanza) use ($service, $node) {
            if ($stanza->from == $service) {
                $instance = Xmpp::getInstance();
                $instance->setResponse(new XmppResponse());
                $instance->send_end_stream();
            }
        });
        
        $this->client->start();
        
        return $this->getResponse();
    }

    public function approveSupscriptions($node, $jid, $subid = '', $allow = true, $service = self::PUBSUB_SERVICE)
    {
        $service = $service . '.' . $this->config['domain'];
        
        $this->client->add_cb('on_auth_success', function() use ($node, $service, $jid, $subid, $allow) {
            $client = Xmpp::getInstance()->getClient();
            $client->xeps['0060']->approve_supscriptions($service, $node, $jid, $subid, $allow);
            
            $instance = Xmpp::getInstance();
            $instance->setResponse(new XmppResponse());
            $instance->send_end_stream();
        });
        
        $this->client->start();
        
        return $this->getResponse();
    }
    
    public function _onUpdateNodeMember($stanza, $node, $service = self::PUBSUB_SERVICE)
    {
        $instance = Xmpp::getInstance();
        $members = $stanza->childrens[0]->childrens[0]->childrens;
        
        foreach ($members as $index => $member) {
            if ($member->attrs['subscription'] != Xmpp::PUBSUB_NODE_SUBSCRIBED) {
                unset($members[$index]);
            }
        }
        
        $total_member = count($members);
        
        $item = $instance->_createTotalMembersItemXml($total_member, Xmpp::PUBSUB_NODE_MEMBER_ITEM_ID);
        $instance->getClient()->xeps['0060']->publish_item($service, $node, $item);
    }
    
    /**
     * Delete Pubsub node
     * 
     * @param string $node
     * @param string $service
     * @return  XmppResponse
     */
    public function deleteNode($node, $service = self::PUBSUB_SERVICE)
    {
        $service = $service . '.' . $this->config['domain'];
        
        // send a create node stanza
        $this->client->add_cb('on_auth_success', function() use ($node, $service) {
        	$client = Xmpp::getInstance()->getClient();
        	$client->xeps['0060']->delete_node($service, $node);
        });
        
        // on success response
        $this->client->add_cb('on_result_iq', function($stanza) use ($service) {
            if ($stanza->from == $service) {
                $instance = Xmpp::getInstance();
                $instance->setResponse(new XmppResponse());
                $instance->getClient()->send_end_stream();
            }
        });
        
        $this->client->start();
        
        return $this->getResponse();
    }
    
    /**
     * Get subscriber list
     * 
     * @param   string  $node
     * @param   string  $service
     * @return  XmppResponse
     */
    public function getSubscriberList($node, $service = self::PUBSUB_SERVICE)
    {
        $service = $service . '.' . $this->config['domain'];
        
        // send a create node stanza
        $this->client->add_cb('on_auth_success', function() use ($node, $service) {
        	$client = Xmpp::getInstance()->getClient();
        	$client->xeps['0060']->get_suscriber_list($service, $node);
        });
        
        // on success response
        $this->client->add_cb('on_result_iq', function($stanza) use ($service) {
            if ($stanza->from == $service) {
                $subscribers = array();
                
                // loop through all subscription item in response stanza
                foreach ($stanza->childrens[0]->childrens[0]->childrens as $supscription) {
                    $jid = new XMPPJid($supscription->attrs['jid']);
                    $subscribers[] = array(
                        'id' => $jid->node,
                        'subscription' => $supscription->attrs['subscription']
                    );
                }
                
                $response = new XmppResponse();
                $response->subscribers = $subscribers;

                $instance = Xmpp::getInstance();
                $instance->setResponse($response);
                $instance->getClient()->send_end_stream();
            }
        });
        
        $this->client->start();
        
        return $this->getResponse();
    }
    
    public function createChatRoom($roomId, $nickname)
    {
        $roomJid = $this->getChatRoomJid($roomId);
        
        // send a create node stanza
        $this->client->add_cb('on_auth_success', function() use ($roomJid, $nickname) {
        	$client = Xmpp::getInstance()->getClient();
            $client->step = 1;
        	$client->xeps['0045']->create_room($roomJid, $nickname);
        });
        
        $this->client->add_cb('on_presence_stanza', function ($stanza) use ($roomJid, $nickname) {
            if ($stanza->from == $roomJid . '/' . $nickname) {
                $client = Xmpp::getInstance()->getClient();
                $client->xeps['0045']->active_room($roomJid);
            }
        });
        
        // on success response
        $this->client->add_cb('on_result_iq', function($stanza) use ($roomJid) {
            if ($stanza->from == $roomJid) {
                $instance = Xmpp::getInstance();
                $response = new XmppResponse;
                $instance->setResponse($response);
                $instance->send_end_stream();
            }
        });
        
        $this->client->start();
        
        return $this->getResponse();
    }
    
    public function grantAdmin($roomId, $members)
    {
        $roomJid = $this->getChatRoomJid($roomId);
        if ( ! is_array($members)) {
            $members = array($members);
        }
        
        $this->client->add_cb('on_auth_success', function() use ($roomJid, $members) {
        	$client = Xmpp::getInstance()->getClient();
        	$client->xeps['0045']->grant_admin($roomJid, $members);
        });
        
        $this->client->add_cb('on_result_iq', function($stanza) use ($roomJid) {
            if ($stanza->from == $roomJid) {
                $instance = Xmpp::getInstance();
                $response = new XmppResponse;
                $instance->setResponse($response);
                $instance->send_end_stream();
            }
        });
        
        $this->client->start();
        
        return $this->getResponse();
    }
    
    public function destroyRoom($roomId)
    {
        $roomJid = $this->getChatRoomJid($roomId);
        
        $this->client->add_cb('on_auth_success', function() use ($roomJid) {
            $client = Xmpp::getInstance()->getClient();
            $client->xeps['0045']->destroy_room($roomJid);
        });
        
        $this->client->add_cb('on_result_iq', function($stanza) use ($roomJid) {
            if ($stanza->from == $roomJid) {
                $instance = Xmpp::getInstance();
                $response = new XmppResponse;
                $instance->setResponse($response);
                $instance->send_end_stream();
            }
        });
        
        $this->client->start();

        return $this->getResponse();
    }
    
    protected function getChatRoomJid($roomId)
    {
        return $roomId . '@conference.' . $this->config['domain'];
    }
    
    public function send_end_stream()
    {
        $this->end = true;
        $this->client->send_end_stream();
    }
    
    public function isEndStream()
    {
        return $this->end;
    }
    
}