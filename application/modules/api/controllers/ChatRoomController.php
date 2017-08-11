<?php

class Api_ChatRoomController extends App_Rest_Controller
{
    
    protected $_businessClass = 'Business_ChatRoom';
    
    protected $_daoClass = 'Dao_ChatRoom';
    
    /**
     * Chat room members Dao
     * 
     * @var Dao_ChatRoomUser
     */
    protected $chatRoomUserDao;
    
    /**
     * Initialize class
     * 
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->chatRoomUserDao = new Dao_ChatRoomUser();
    }
    
    /**
     * Get the business model
     * 
     * @return Business_ChatRoom
     */
    public function getBusiness()
    {
        return parent::getBusiness();
    }
    
    /**
     * Get the Dao object
     * 
     * @return Dao_ChatRoom
     */
    public function getDao()
    {
        return parent::getDao();
    }
    
    /**
     * User join a chat 
     */
    public function joinAction()
    {
        $roomId = $this->_getParam('id');
        $nickname = $this->_getParam('nick_name');
        if ( ! $roomId OR ! $nickname) {
            $this->badRequestAction();
        }
        
        // validate the room id
        if (strlen($roomId) > 100) {
            $this->failure(ERROR_FORM_VALIDATION_FAILURE);
        }
        
        $userDto = Zend_Registry::get('api_user');
        $result = $this->getBusiness()->join($roomId, $userDto, $nickname);
        
        $this->fromArray($result);
    }
    
    /**
     * User leave action
     */
    public function leaveAction()
    {
        $roomId = $this->_getRoomId();
        $userDto = Zend_Registry::get('api_user');
        $result = $this->getBusiness()->leave($roomId, $userDto);
        
        $this->fromArray($result);
    }
    
    /**
     * Inivite another user to join a chat room action
     */
    public function inviteAction()
    {
    	$roomId = $this->_getRoomId();
    	$userDto = Zend_Registry::get('api_user');
    	$nickname = $this->_getParam('nick_name');
    	$invited = $this->getMultiUser($this->_getParam('user_list'));
    	
    	$result = $this->getBusiness()->invite($roomId, $userDto, $invited, $nickname);
    	$this->fromArray($result);
    }
    
    /**
     * Return all members of chat room
     */
    public function getAction($state = array(Dto_ChatRoomUser::STATE_JOIN, Dto_ChatRoomUser::STATE_INVITED))
    {
        $query = $this->getRequest()->getQuery();
        $query['room_id'] = $this->_getRoomId();
        $query['state'] = $state;
        
        $members = $this->chatRoomUserDao->fetchAll($this->chatRoomUserDao->doFilter($query));
        
        $this->success(array('members' => $members->toEndUserArray()));
    }
    
    /**
     * Retrieve left members of chat room
     */
    public function leftAction()
    {
        return $this->getAction(array(Dto_ChatRoomUser::STATE_LEFT));
    }
    
    /**
     * Retrieve all chat rooms that user is currently invited to join
     */
    public function invitedRoomAction()
    {
    	$userDto = Zend_Registry::get('api_user');
    	$rooms = $this->getBusiness()->getRoomByState($userDto, Dto_ChatRoomUser::STATE_INVITED);
    	
    	$roomArray = $rooms->toArray(array('room_id', 'created_at'));
    	$this->success(array('rooms' => $roomArray));
    }
    
    /**
     * Retrieve all chat rooms that user is currently join
     */
    public function joinRoomAction()
    {
    	$userDto = Zend_Registry::get('api_user');
    	$rooms = $this->getBusiness()->getRoomByState($userDto, Dto_ChatRoomUser::STATE_JOIN);
    	
    	$roomArray = $rooms->toArray(array('room_id', 'created_at'));
    	$this->success(array('rooms' => $roomArray));
    }
    
    /**
     * Retrieve room id from request and validate
     * Throw 400 Bad Request if the room id does not valid
     * 
     * @return  string
     * @throws  400 Bad Request
     */
    protected function _getRoomId()
    {
        $roomId = $this->_getParam('id');
        if ( ! $roomId) {
            $this->badRequestAction();
        }
        
        return $roomId;
    }
    
}