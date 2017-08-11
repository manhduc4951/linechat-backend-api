<?php

/**
 * Business_ChatRoom class
 * 
 * @package LineChatApp
 * @subpackage Business
 * @author duyld
 */
class Business_ChatRoom extends Business_Abstract
{
    /**
     * Chat room members Dao
     * 
     * @var Dao_ChatRoomUser
     */
    protected $chatRoomUserDao;
    
    /**
     * Constructor
     * 
     * @return Business_ChatRoom
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->chatRoomUserDao = new Dao_ChatRoomUser();
    }
    
    public function create(Dto_User $userDto)
    {
        $client = XmppFactory::create($userDto);
        $roomId = $this->_generateRoomId($userDto);
        $response = $client->createChatRoom($roomId, $userDto->nick_name);
        
        if ($response->isFailure()) {
            return $this;
        }
        
        return array('status' => true, 'room_id' => $roomId);
    }
    
    /**
     * User join a chat room, if user alread join to a chat room, update the user nick name
     * 
     * @param   string      $roomId
     * @param   Dto_user    $userDto
     * @param   string      $nickname
     * @return  array       Result array
     */
    public function join($roomId, Dto_User $userDto, $nickname)
    {
        $chatRoomUser = $this->getChatRoomUser($roomId, $userDto);
        
        $this->_updateState($roomId, $userDto, array(
        	'nick_name' => $nickname,
        	'state'		=> Dto_ChatRoomUser::STATE_JOIN,
        ));
        
        return array('status' => true);
    }
    
    /**
     * Update the user state of the relationship with chat room
     * 
     * @param string $roomId
     * @param Dto_User $userDto
     * @param array $data		The data to be updated
     * @return boolean
     */
    protected function _updateState($roomId, Dto_User $userDto, array $data)
    {
    	$chatRoomUser = $this->getChatRoomUser($roomId, $userDto);
    	if ( ! $chatRoomUser) {
    		$chatRoomUser = new Dto_ChatRoomUser();
    		$chatRoomUser->room_id = $roomId;
    		$chatRoomUser->unique_id = $userDto->unique_id;
    		$chatRoomUser->setFromArray($data);
    	
    		$this->chatRoomUserDao->insert($chatRoomUser);
    		return true;
    	}
    	
    	$chatRoomUser->setFromArray($data);
    	$this->chatRoomUserDao->update($chatRoomUser);
    	
    	return true;
    }
    
    /**
     * User leave a room
     * 
     * @param   string      $roomId
     * @param   Dto_user    $userDto
     * @return  array       Result array
     */
    public function leave($roomId, Dto_User $userDto)
    {
        $chatRoomUser = $this->getChatRoomUser($roomId, $userDto);
        if ($chatRoomUser) {
            $chatRoomUser->state = Dto_ChatRoomUser::STATE_LEFT;
            $chatRoomUser->created_at = Qsoft_Helper_Datetime::current();
            $this->chatRoomUserDao->update($chatRoomUser);
        }
        
        return array('status' => true);
    }
    
    /**
     * Invite user to join a chat room
     * 
     * @param string $roomId
     * @param Dto_User $userDto
     * @param string|array $users
     * @param string|array $nick_name
     * @return array
     */
    public function invite($roomId, Dto_User $userDto, $users, $nick_name)
    {
    	// the user must be member of group to invite the others
    	if ( ! $this->isJoin($roomId, $userDto)) {
    		return array('status' => false, 'error_code' => ERROR_AUTHORIZE_DENY);
    	}
    	
    	if ( ! is_array($users)) {
    		$users = array($users);
    	}
    	
    	if ( ! is_array($nick_name)) {
    		$nick_name = array($nick_name);
    	}
    	
    	// begin transaction
    	$this->userDao->getAdapter()->beginTransaction();
    	
    	foreach ($users as $index => $invitedDto) {
    		// check if user is already member
    		if ($this->isJoin($roomId, $invitedDto)) {
    			continue;
    		}
    		
    		$this->_updateState($roomId, $invitedDto, array(
    			'state' => Dto_ChatRoomUser::STATE_INVITED,
    			'created_at' => Qsoft_Helper_Datetime::current(),
    			'nick_name' => (isset($nick_name[$index]) ? $nick_name[$index] : ""),
    		));
    	}
    	
    	$this->userDao->getAdapter()->commit();
    	return array('status' => true);
    }
    
    /**
     * Return chat room user dto match with this criteria
     * 
     * @param   Dto_user    $userDto
     * @param   string      $nickname
     * @return  Dto_ChatRoomUser
     */
    public function getChatRoomUser($roomId, Dto_User $userDto)
    {
        return $this->chatRoomUserDao->fetchOneBy(array(
            'room_id' => $roomId,
            'unique_id' => $userDto->unique_id,
        ));
    }
    
    /**
     * Retrieve all chat rooms that user is currently invited to join
     * 
     * @param Dto_User $userDto
     * @param string $state
     * @return Qsoft_Db_Table_Rowset
     */
    public function getRoomByState(Dto_User $userDto, $state = Dto_ChatRoomUser::STATE_JOIN)
    {
    	return $this->chatRoomUserDao->fetchAllBy(array(
    			'unique_id' => $userDto->unique_id,
    			'state' => $state,
    	));
    }
    
    /**
     * Check whether user is already join to chat room
     * 
     * @param string $roomId
     * @param Dto_User $userDto
     * @return boolean
     */
    public function isJoin($roomId, Dto_User $userDto)
    {
    	return (boolean) $this->chatRoomUserDao->fetchOneBy(array(
            'room_id' => $roomId,
            'unique_id' => $userDto->unique_id,
    		'state'	=> Dto_ChatRoomUser::STATE_JOIN,
        ));
    }
    
    public function delete($roomId)
    {
        // begin transaction
        $this->chatRoomUserDao->getAdapter()->beginTransaction();
        
        // remove all chat room users
        $this->chatRoomUserDao->deleteBy('room_id', $roomId);
        
        // delete from xmpp
        // get the owner of chat room
        $mucAffiliationDao = Dao_Chat_Factory::create('MucAffiliation');
        $mucAffiliationDto = $mucAffiliationDao->fetchOneBy(array(
            'roomID' => $roomId, 'affiliation' => Xmpp::MUC_AFFILIATION_OWNER
        ));
        
        // return unknown error from xmpp if cannot found the owner
        if ( ! $mucAffiliationDto) {
            echo 'no owner';
            $this->chatRoomUserDao->getAdapter()->rollBack();
            return array('status' => false, 'error_code' => ERROR_UNKNOWN_FROM_CHAT_SERVER);
        }
        
        // factory the client
        $jid = new XMPPJid($mucAffiliationDto->jid);
        $ownerDto = $this->userDao->fetchOneBy('unique_id', $jid->node);
        
        if ( ! $ownerDto) {
            echo 'cannot find user';
            $this->chatRoomUserDao->getAdapter()->rollBack();
            return array('status' => false, 'error_code' => ERROR_UNKNOWN_FROM_CHAT_SERVER);
        }
        
        // call api to destroy room
        $client = XmppFactory::create($ownerDto);
        $response = $client->destroyRoom($roomId);
        
        if ($response->isFailure()) {
            echo 'xmpp fail';
            $this->chatRoomUserDao->getAdapter()->rollBack();
            return array('status' => false, 'error_code' => ERROR_UNKNOWN_FROM_CHAT_SERVER);
        }
        
        // commit it out
        $this->chatRoomUserDao->getAdapter()->commit();
        return array('status' => true);
    }
    
    /**
     * Generate a random room id
     * 
     * @return  string
     */
    protected function _generateRoomId(Dto_User $userDto)
    {
        return $userDto->id . microtime(true) * 10000;
    }
    
}