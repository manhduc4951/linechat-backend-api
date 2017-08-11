<?php

/**
 * Api_Bootstrap
 * 
 * @package LCA450
 * @subpackage Api Module Bootstrap
 */
class Api_Bootstrap extends Zend_Application_Module_Bootstrap
{

    /**
     * Constructor
     *
     * @param  Zend_Application|Zend_Application_Bootstrap_Bootstrapper $application
     * @return void
     */
    public function __construct($application)
    {
        parent::__construct($application);
        
        // load module config
        $configPath = APPLICATION_PATH . '/modules/api/configs/config.ini';
        Zend_Registry::set('api_config', new Zend_Config_Ini($configPath));
    }
    
    /**
     * Initialize controller plugins 
     */
    protected function _initPlugins()
    {
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(
            new Qsoft_Controller_Plugin_Rest_Auth()
        );
    }
    
    /**
     * Initialize the RESTful service routes
     */
    protected function _initRestRoute()
    {
        $config = Zend_Registry::get('api_config');
        $frontController = Zend_Controller_Front::getInstance();
        $requestUri = $frontController->getRequest()->getRequestUri();
        $moduleUri = $frontController->getRequest()->getBaseUrl() . '/' . $config->auth->moduleName;
        
        // check current module to init rest routes
        if (substr($requestUri, 0, strlen($moduleUri)) == $moduleUri) {
            $restRoute = new Zend_Rest_Route(
                $frontController,
                array(),
                array('api' => array(
                    'user', 'group', 'file-transfer', 'lifelog', 'chat-background',
                    'lifelog-comment', 'lifelog-like', 'chat-room', 'shake', 'stamp'
                ))
            );
            
            $frontController->getRouter()->addRoute('rest', $restRoute);
            
            // all custom routes
            $frontController->getRouter()->addRoutes(array(
            	// pre register action
                'pre_register' => new Zend_Controller_Router_Route(
                    'api/pre-register',
                    array('module' => 'api', 'controller' => 'user', 'action' => 'pre-register')
                ),
                // find an user with user id
                'find_user' => new Zend_Controller_Router_Route(
                    'api/user/find',
                    array('module' => 'api', 'controller' => 'user', 'action' => 'find')
                ),
                // upload user image
                'upload_user_image' => new Zend_Controller_Router_Route(
                    'api/user/image',
                    array('module' => 'api', 'controller' => 'user', 'action' => 'image')
                ),
                // invite users to group
                'invite_to_group' => new Zend_Controller_Router_Route(
                    'api/group/invite/:id',
                    array('module' => 'api', 'controller' => 'group', 'action' => 'invite')
                ),
                // search all groups
                'search_group' => new Zend_Controller_Router_Route(
                    'api/group/search',
                    array('module' => 'api', 'controller' => 'group', 'action' => 'search')
                ),
                // user request to join group
                'join_group' => new Zend_Controller_Router_Route(
                    'api/group/join/:id',
                    array('module' => 'api', 'controller' => 'group', 'action' => 'join')
                ),
                // user reject to join group
                'reject_invite_group' => new Zend_Controller_Router_Route(
                    'api/group/reject/:id',
                    array('module' => 'api', 'controller' => 'group', 'action' => 'invite-reject')
                ),
                // user request to leave group
                'leave_group' => new Zend_Controller_Router_Route(
                    'api/group/leave/:id',
                    array('module' => 'api', 'controller' => 'group', 'action' => 'leave')
                ),
                // owner accept user to join group
                'accept_join_group' => new Zend_Controller_Router_Route(
                    'api/group/join-accept/:id',
                    array('module' => 'api', 'controller' => 'group', 'action' => 'join-accept')
                ),
                'reject_join_group' => new Zend_Controller_Router_Route(
                    'api/group/join-reject/:id',
                    array('module' => 'api', 'controller' => 'group', 'action' => 'join-reject')
                ),
                // user join a chat room
                'join_chat_room' => new Zend_Controller_Router_Route(
                    'api/chat-room/join/:id',
                    array('module' => 'api', 'controller' => 'chat-room', 'action' => 'join')
                ),
                // user leave a chat room
                'leave_chat_room' => new Zend_Controller_Router_Route(
                    'api/chat-room/leave/:id',
                    array('module' => 'api', 'controller' => 'chat-room', 'action' => 'leave')
                ),
                // invite another user to a chat room
                'invite_to_chat_room' => new Zend_Controller_Router_Route(
            		'api/chat-room/invite/:id',
            		array('module' => 'api', 'controller' => 'chat-room', 'action' => 'invite')
                ),
                // get chat room left members
                'chat_room_left_members' => new Zend_Controller_Router_Route(
                    'api/chat-room/left/:id',
                    array('module' => 'api', 'controller' => 'chat-room', 'action' => 'left')
                ),
                // get all chat room that user has been invited to join
                'get_invited_chat_room_of_user' => new Zend_Controller_Router_Route(
            		'api/chat-room/invited-room',
            		array('module' => 'api', 'controller' => 'chat-room', 'action' => 'invited-room')
                ),
                // get all chat room that user has been join
                'get_join_chat_room_of_user' => new Zend_Controller_Router_Route(
            		'api/chat-room/join-room',
            		array('module' => 'api', 'controller' => 'chat-room', 'action' => 'join-room')
                ),
                // get or update privacy settings
                'user_privacy_settings' => new Zend_Controller_Router_Route(
            		'api/user/privacy',
            		array('module' => 'api', 'controller' => 'user', 'action' => 'get-privacy')
                ),
                // get or update notification settings
                'user_notification_settings' => new Zend_Controller_Router_Route(
            		'api/user/notification',
            		array('module' => 'api', 'controller' => 'user', 'action' => 'get-notification')
                ),
                // get or update public home settings
                'user_public_home_settings' => new Zend_Controller_Router_Route(
                    'api/user/public-home-setting',
                    array('module' => 'api', 'controller' => 'user', 'action' => 'get-public-home-setting')
                ),
                // get or update hide settings
                'user_hide_settings' => new Zend_Controller_Router_Route(
                    'api/user/hide-setting',
                    array('module' => 'api', 'controller' => 'user', 'action' => 'get-hide-setting')
                ),
                // get user synchronize data
                'get_synchronize_data' => new Zend_Controller_Router_Route(
               		'api/user/sync',
                	array('module' => 'api', 'controller' => 'user', 'action' => 'sync')
                ),
                // get lifelogs of friends
                'get_friend_lifelogs' => new Zend_Controller_Router_Route(
                    'api/lifelog/friend',
                    array('module' => 'api', 'controller' => 'lifelog', 'action' => 'friend')
                ),
                // download zip file or stamp item image
                'stamp_download_zip' => new Zend_Controller_Router_Route(
                    'api/stamp/download/:zip_file',
                    array('module' => 'api', 'controller' => 'stamp', 'action' => 'download')
                ),
                'stamp_download_image' => new Zend_Controller_Router_Route(
                    'api/stamp/download/:id/:image',
                    array('module' => 'api', 'controller' => 'stamp', 'action' => 'download')
                ),
                // check user is blocked by another user service
                'user_is_blocked_by' => new Zend_Controller_Router_Route(
                    'api/user/is-blocked-by',
                    array('module' => 'api', 'controller' => 'user', 'action' => 'is-blocked-by')
                ),

                'shop' => new Zend_Controller_Router_Route(
                    'api/shop/',
                    array('module' => 'api', 'controller' => 'shop', 'action' => 'index')
                ),
                'gift-info' => new Zend_Controller_Router_Route(
                    'api/shop/gift-info',
                    array('module' => 'api', 'controller' => 'shop', 'action' => 'gift-info')
                ),
                'send-gift' => new Zend_Controller_Router_Route(
                    'api/shop/send-gift',
                    array('module' => 'api', 'controller' => 'shop', 'action' => 'send-gift')
                ),
                'discard-gift' => new Zend_Controller_Router_Route(
                    'api/shop/discard-gift',
                    array('module' => 'api', 'controller' => 'shop', 'action' => 'discard-gift')
                ),
                'item-info' => new Zend_Controller_Router_Route(
                    'api/shop/item-info',
                    array('module' => 'api', 'controller' => 'shop', 'action' => 'item-info')
                ),
                'item-detail' => new Zend_Controller_Router_Route(
                    'api/shop/item-detail',
                    array('module' => 'api', 'controller' => 'shop', 'action' => 'item-detail')
                ),
                'stamp-info' => new Zend_Controller_Router_Route(
                    'api/shop/stamp-info',
                    array('module' => 'api', 'controller' => 'shop', 'action' => 'stamp-info')
                ),
                'stamp-detail' => new Zend_Controller_Router_Route(
                    'api/shop/stamp-detail',
                    array('module' => 'api', 'controller' => 'shop', 'action' => 'stamp-detail')
                ),
                'gift-detail' => new Zend_Controller_Router_Route(
                    'api/shop/gift-detail',
                    array('module' => 'api', 'controller' => 'shop', 'action' => 'gift-detail')
                ),
                'get-point' => new Zend_Controller_Router_Route(
                    'api/shop/get-point',
                    array('module' => 'api', 'controller' => 'shop', 'action' => 'get-point')
                ),
                'spend-point' => new Zend_Controller_Router_Route(
                    'api/shop/spend-point',
                    array('module' => 'api', 'controller' => 'shop', 'action' => 'spend-point')
                ),
                'add-point' => new Zend_Controller_Router_Route(
                    'api/shop/add-point',
                    array('module' => 'api', 'controller' => 'shop', 'action' => 'add-point')
                ),
                'set-gift' => new Zend_Controller_Router_Route(
                    'api/shop/set-gift',
                    array('module' => 'api', 'controller' => 'shop', 'action' => 'set-gift')
                ),
                'set-item' => new Zend_Controller_Router_Route(
                    'api/shop/set-item',
                    array('module' => 'api', 'controller' => 'shop', 'action' => 'set-item')
                ),
                'set-stamp' => new Zend_Controller_Router_Route(
                    'api/shop/set-stamp',
                    array('module' => 'api', 'controller' => 'shop', 'action' => 'set-stamp')
                ),
                'set-showed-gift' => new Zend_Controller_Router_Route(
                    'api/shop/set-showed-gift',
                    array('module' => 'api', 'controller' => 'shop', 'action' => 'set-showed-gift')
                ),
                'all' => new Zend_Controller_Router_Route(
                    'api/shop/all',
                    array('module' => 'api', 'controller' => 'shop', 'action' => 'all')
                ),
                'get-receive-present' => new Zend_Controller_Router_Route(
                    'api/shop/get-receive-present',
                    array('module' => 'api', 'controller' => 'shop', 'action' => 'get-receive-present')
                ),
                'received-present' => new Zend_Controller_Router_Route(
                    'api/shop/received-present',
                    array('module' => 'api', 'controller' => 'shop', 'action' => 'received-present')
                ),
                'get-point-master' => new Zend_Controller_Router_Route(
                    'api/shop/get-point-master',
                    array('module' => 'api', 'controller' => 'shop', 'action' => 'get-point-master')
                ),
                'get-roulette-master' => new Zend_Controller_Router_Route(
                    'api/shop/get-roulette-master',
                    array('module' => 'api', 'controller' => 'shop', 'action' => 'get-roulette-master')
                ),
                'get-picollection' => new Zend_Controller_Router_Route(
                    'api/shop/get-picollection',
                    array('module' => 'api', 'controller' => 'shop', 'action' => 'get-picollection')
                ),
                'evaluation' => new Zend_Controller_Router_Route(
                    'api/talk-shake/evaluation',
                    array('module' => 'api', 'controller' => 'talk-shake', 'action' => 'evaluation')
                ),
                'report' => new Zend_Controller_Router_Route(
                    'api/talk-shake/report',
                    array('module' => 'api', 'controller' => 'talk-shake', 'action' => 'report')
                ),
                'get-report-template' => new Zend_Controller_Router_Route(
                    'api/talk-shake/get-report-template',
                    array('module' => 'api', 'controller' => 'talk-shake', 'action' => 'get-report-template')
                ),
                'get-availableinfo' => new Zend_Controller_Router_Route(
                    'api/talk-shake/get-availableinfo',
                    array('module' => 'api', 'controller' => 'talk-shake', 'action' => 'get-availableinfo')
                ),
                'get-dice-info' => new Zend_Controller_Router_Route(
                    'api/talk-shake/get-dice-info',
                    array('module' => 'api', 'controller' => 'talk-shake', 'action' => 'get-dice-info')
                ),
                'start-shake' => new Zend_Controller_Router_Route(
                    'api/talk-shake/start-shake',
                    array('module' => 'api', 'controller' => 'talk-shake', 'action' => 'start-shake')
                ),
                'end-shake' => new Zend_Controller_Router_Route(
                    'api/talk-shake/end-shake',
                    array('module' => 'api', 'controller' => 'talk-shake', 'action' => 'end-shake')
                ),
                'start-now' => new Zend_Controller_Router_Route(
                    'api/talk-shake/start-now',
                    array('module' => 'api', 'controller' => 'talk-shake', 'action' => 'start-now')
                ),
                'end-now' => new Zend_Controller_Router_Route(
                    'api/talk-shake/end-now',
                    array('module' => 'api', 'controller' => 'talk-shake', 'action' => 'end-now')
                ),
                'maching-start' => new Zend_Controller_Router_Route(
                    'api/talk-shake/maching-start',
                    array('module' => 'api', 'controller' => 'talk-shake', 'action' => 'maching-start')
                ),
                'maching-end' => new Zend_Controller_Router_Route(
                    'api/talk-shake/maching-end',
                    array('module' => 'api', 'controller' => 'talk-shake', 'action' => 'maching-end')
                ),
                'maching-search' => new Zend_Controller_Router_Route(
                    'api/talk-shake/maching-search',
                    array('module' => 'api', 'controller' => 'talk-shake', 'action' => 'maching-search')
                ),
            ));
        }
    }
}