<?php

/**
 * Dao_Chat_PubsubSupscription
 * 
 * @package Dao_Chat
 * @author duyld
 */
class Dao_Chat_PubsubSupscription extends Qsoft_Db_Table_Abstract
{

    protected $_name = 'ofPubsubSubscription';
    
    protected $_rowClass = 'Dto_Chat_PubsubSupscription';
    
    /**
     * get list of node id that subscibed by provided jid
     * 
     * @param   string $bareJid
     * @return  array
     */
    public function fetchSubscribed($bareJid, $serviceId = Xmpp::PUBSUB_SERVICE)
    {
        $select = $this->select()
            ->from($this->_name, 'nodeId')
            ->where('jid = ?', $bareJid)
            ->where('serviceID = ?', $serviceId);
        
        return array_keys($this->getAdapter()->fetchAssoc($select));
    }
    
    /**
     * Get list of subscriber that subscribed to a node
     * 
     * @param   Dto_UserGroup   $groupDto
     * @return  Qsoft_Db_Table_Abstract
     */
    public function fetchSubscriberList(Dto_UserGroup $groupDto, $state = Xmpp::PUBSUB_NODE_SUBSCRIBED)
    {
        $select = $this->select()
            ->from($this->_name, array('jid', 'state'))
            ->where('serviceID = ?', Xmpp::PUBSUB_SERVICE)
            ->where('nodeID = ?', $groupDto->node_id)
            ->where('state = ?', $state)
        ;
        
        return $this->fetchAll($select);
    }
    
}
