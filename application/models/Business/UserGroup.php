<?php

/**
 * Business_UserGroup class
 * 
 * @package LineChatApp
 * @subpackage Business
 * @author duyld
 */
class Business_UserGroup extends Business_Abstract
{
    /**
     * Group DAO
     * 
     * @var Dao_UserGroup
     */
    protected $groupDao;
    
    /**
     * Group Invite DAO
     * 
     * @var Dao_GroupInvite
     */
    protected $groupInviteDao;
    
    /**
     * Chat subscription Dao
     * 
     * @var Dao_Chat_PubsubSupscription
     */
    protected $chatSubscriptionDao;
    
    /**
     * Constructor
     * 
     * @return Business_User
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->groupDao = new Dao_UserGroup();
        $this->groupInviteDao = new Dao_GroupInvite();
        $this->chatSubscriptionDao = Dao_Chat_Factory::create('Dao_Chat_PubsubSupscription');
        
        return $this;
    }
    
    /**
     * Create new user group instance
     * 
     * @param   Dto_UserGroup   $groupDto
     * @param   Dto_User        $userDto    The owner of new group
     * @param   array           $members	Array of Dto_User
     * @return  array   Result array
     */
    public function create(Dto_UserGroup $groupDto, Dto_User $userDto, $members = array())
    {
        // do some configure to node
        $nodeConfigurations = $this->_getNodeConfiguration($groupDto);
        
        // create Xmpp node
        $client = XmppFactory::create($userDto);
        $response = $client->createNode(null, Xmpp::PUBSUB_SERVICE,
            $groupDto->toEndUserArray(), $nodeConfigurations);
        
        if ($response->isFailure()) {
            return $this->_readXmppResponse($response);
        }
        
        // create new group instance in database with created node id
        $groupDto->user_id = $userDto->id;
        $groupDto->node_id = $response->node_id;
        $this->groupDao->insert($groupDto);
        
        // invite member to join group
        $this->invite($members, $groupDto, $userDto);
        
        $groupDto->addColumn('unique_id', $userDto->unique_id);
        return array('status' => true);
    }
    
    /**
     * Update an user group instance
     * 
     * @param   Dto_UserGroup   $groupDto
     * @param   Dto_User        $userDto
     * @param   array           $members	Array of Dto_User
     * @return  array   Result array
     */
    public function update(Dto_UserGroup $groupDto, Dto_User $userDto, $members = array())
    {
        // get the old image to delete after complete
        $oldGroupDto = $this->groupDao->fetchOne($groupDto->id);
        
        // begin the transaction
        $this->groupDao->getAdapter()->beginTransaction();
        $this->groupDao->update($groupDto);
        
        // update the chat database also
        $nodeConfigurations = $this->_getNodeConfiguration($groupDto);
		$client = $this->_factoryXmppClientFromGroup($groupDto);
		
        $response = $client->updateNode($groupDto->node_id, $groupDto->toEndUserArray(), $nodeConfigurations);
        
        if ($response->isFailure()) {
            $this->groupDao->getAdapter()->rollBack();
            return $this->_readXmppResponse($response);
        }
        
        // database transaction fine, unlink the old image if exist
        if ($oldGroupDto->image != $groupDto->image AND $oldGroupDto->image) {
            @unlink($oldGroupDto->getLargeImagePath());
            @unlink($oldGroupDto->getSmallImagePath());
        }
        
        // invite member to join group
        $this->invite($members, $groupDto, $userDto);
        
        $this->groupDao->getAdapter()->commit();
        return array('status' => true);
    }
    
    /**
     * Invite member to join group
     * 
     * @param   mixed       $members    a single or multi Dto_User instances
     * @param   Dto_Group   $groupDto
     * @param   Dto_User    $inviter    Inviter can be anyone, not just group owner
     * @return  array   Result array
     */
    public function invite($members, Dto_UserGroup $groupDto, Dto_User $inviter)
    {
        // check that user has permission to invite
        if ( ! $this->isMember($inviter, $groupDto)) {
            return array('status' => false, 'error_code' => ERROR_AUTHORIZE_DENY);
        }
        
        if (empty($members)) {
            return array('status' => true);
        }
        
        if ( ! is_array($members)) {
            $members = array($members);
        }
        
		// loop through all members to be invited
        foreach ($members as $index => $member) {
			// cancel if user is already member
			if ($this->isMember($member, $groupDto)) {
				unset($members[$index]);
				continue;
			}
            
            // ignore update to database if user already invited
            if ($this->isInvited($member, $groupDto)) continue;
            
            $groupInviteDto = new Dto_GroupInvite();
            $groupInviteDto->node_id = $groupDto->node_id;
            $groupInviteDto->user_id = $member->id;
            $this->groupInviteDao->insert($groupInviteDto);
        }
        
        // send message to all invited user
        $members = $this->toBareJid($members);
        $client = XmppFactory::create($inviter);
        $client->inviteToNode($members, $groupDto->node_id, $groupDto->toEndUserArray());
        
        // update the member of group
        $client = $this->_factoryXmppClientFromGroup($groupDto);
        $client->updateMember($groupDto->node_id);
        
        return array('status' => true);
    }
	
	/**
	 * User reject an invitation to join group
	 * 
	 * @param   Dto_Group   $groupDto
     * @param   Dto_User    $userDto		The user want to reject
	 * @return	array 		Result array
	 */
	public function rejectInvitation(Dto_UserGroup $groupDto, Dto_User $userDto)
	{
		// delete the invitation records
		$this->deleteInvitation($groupDto, $userDto);
		
		// update member list to alert changes
		$client = $this->_factoryXmppClientFromGroup($groupDto);
        $client->updateMember($groupDto->node_id);
		
		return array('status' => true);
	}
	
	/**
     * Owner of group accept user to join group if have request
     * 
     * @param   mixed       $members    a single or multi Dto_User instances
     * @param   Dto_Group   $groupDto
	 * @param   Dto_User    $userDto	The user that want to perform this action
     * @return  array   Result array
     */
	public function acceptJoinGroup($members, Dto_UserGroup $groupDto, Dto_User $userDto)
	{
		if (empty($members)) {
            return array('status' => true);
        }
        
        if ( ! is_array($members)) {
            $members = array($members);
        }
		
		// check the permisson to accept join
        if ( ! $this->isOwnerOfGroup($userDto, $groupDto)) {
            return array('status' => false, 'error_code' => ERROR_AUTHORIZE_DENY);
        }
		
		return $this->_updateAffiliation($members, $groupDto);
	}
    
    /**
     * Owner of group reject user to join group if have request
     * 
     * @param   Dto_User    $memberDto 
     * @param   string      $subid      Subscription id from xmpp
     * @param   Dto_Group   $groupDto
     * @param   Dto_User    $userDto    The user that want to perform this action
     * @return  array   Result array
     */
    public function rejectJoinGroup(Dto_User $memberDto, $subid, Dto_UserGroup $groupDto, Dto_User $userDto)
    {
        // check the permisson to reject join
        if ( ! $this->isOwnerOfGroup($userDto, $groupDto)) {
            return array('status' => false, 'error_code' => ERROR_AUTHORIZE_DENY);
        }
        
        $client = XmppFactory::create($userDto);
        $response = $client->approveSupscriptions(
            $groupDto->node_id,
            $this->toBareJid($memberDto),
            $subid,
            false
        );
        
        // update member list to alert changes
        $client = $this->_factoryXmppClientFromGroup($groupDto);
        $client->updateMember($groupDto->node_id);
        
        return $this->_readXmppResponse($response);
    }
    
    /**
     * Check whether user was invited to a group instance
     * 
     * @param   Dto_User        $userDto
     * @param   Dto_UserGroup   $groupDto
     * @return  boolean
     */
    public function isInvited(Dto_User $userDto, Dto_UserGroup $groupDto)
    {
        return (boolean) $this->groupInviteDao->fetchOneBy(array(
            'node_id' => $groupDto->node_id,
            'user_id' => $userDto->id
        ));
    }
	
	/**
     * Check whether user is member of group or not
     * 
     * @param   Dto_User        $userDto
     * @param   Dto_UserGroup   $groupDto
     * @return  boolean
     */
	public function isMember(Dto_User $userDto, Dto_UserGroup $groupDto)
	{
		return (boolean) $this->chatSubscriptionDao->fetchOneBy(array(
			'nodeID' => $groupDto->node_id,
            'jid' => XmppFactory::createBareJid($userDto->unique_id),
            'state' => Xmpp::PUBSUB_NODE_SUBSCRIBED
		));
	}
	
	/**
     * Check whether user is pending to be member
     * 
     * @param   Dto_User        $userDto
     * @param   Dto_UserGroup   $groupDto
     * @return  boolean
     */
	public function isPending(Dto_User $userDto, Dto_UserGroup $groupDto)
	{
		return (boolean) $this->chatSubscriptionDao->fetchOneBy(array(
			'nodeID' => $groupDto->node_id,
            'jid' => XmppFactory::createBareJid($userDto->unique_id),
            'state' => Xmpp::PUBSUB_NODE_PENDING
		));
	}
	
	/**
	 * Check whether user is owner of a group
	 */
	public function isOwnerOfGroup(Dto_User $userDto, Dto_UserGroup $groupDto)
	{
		return ($userDto->id == $groupDto->user_id);
	}
    
    /**
     * Get xmpp node configure as array
     * 
     * @param   Dto_Group   $groupDto
     * @return  array
     */
    protected function _getNodeConfiguration(Dto_UserGroup $groupDto)
    {
        $accessModel = $groupDto->isAutoApprove() ?
            Xmpp::PUBSUB_NODE_ACCESS_MODEL_OPEN : Xmpp::PUBSUB_NODE_ACCESS_MODEL_AUTHORIZE;
        
        $nodeConfigurations = array(
            'access_model' => $accessModel,
        );
        
        return $nodeConfigurations;
    }
    
    /**
     * Delete a group
     * 
     * @param   Dto_UserGroup   $groupDto
     * @param   Dto_User        $userDto
     * @return  array
     */
    public function delete(Dto_UserGroup $groupDto, Dto_User $userDto)
    {
        // check the permisson to delete
        if ( ! $this->isOwnerOfGroup($userDto, $groupDto)) {
            return array('status' => false, 'error_code' => ERROR_AUTHORIZE_DENY);
        }
        
        // start transaction
        $this->groupDao->getAdapter()->beginTransaction();
        
        $this->groupDao->delete($groupDto);
        
        $client = XmppFactory::create($userDto);
        $response = $client->deleteNode($groupDto->node_id);
        
        if ($response->isFailure()) {
            $this->groupDao->getAdapter()->rollBack();
            return $this->_readXmppResponse($response);
        }
        
        $this->groupDao->getAdapter()->commit();
        if ($groupDto->image) {
            @unlink($groupDto->getLargeImagePath());
            @unlink($groupDto->getSmallImagePath());
        }
        
        return array('status' => true);
    }
    
    /**
     * User subscribe to group
     * 
     * @param   Dto_UserGroup   $groupDto
     * @param   Dto_User        $userDto
     * @return  array
     */
    public function subscribe(Dto_UserGroup $groupDto, Dto_User $userDto)
    {
        // if group is auto approved, just set the user affiliation
        if ($groupDto->isAutoApprove()) {
            return $this->_updateAffiliation($userDto, $groupDto);
        }
        
        // if not auto approved, subscribe by xmpp service
        return $this->_subscribe($userDto, $groupDto);
    }
    
	/**
	 * Update the affiliation for multi users
	 * 
	 * @param	mixed			$users		array of Dto_User
	 * @param	Dto_UserGroup	$groupDto
	 * @return	array 	Result array
	 */
    protected function _updateAffiliation($users, Dto_UserGroup $groupDto)
    {
        // turn user data to expected format
        if ( ! is_array($users)) {
            $users = array($users);
        }
        
		// begin transaction
		$this->groupDao->getAdapter()->beginTransaction();
		
		// user was subsribed and become a publisher must be removed from invited list
        foreach ($users as $userDto) {
        	$this->deleteInvitation($groupDto, $userDto);
        }
		
		// create an array that contains bare id or subscribers
        $bareJids = $this->toBareJid($users);
		$client = $this->_factoryXmppClientFromGroup($groupDto);
        
        $response = $client->setNodeAffiliation(
            $groupDto->node_id,
            $bareJids,
            Xmpp::PUBSUB_NODE_AFFILIATION_PUBLISHER
        );
		
		if ($response->isFailure()) {
			$this->groupDao->getAdapter()->rollBack();
			return $this->_readXmppResponse($response);
		}
		
		$this->groupDao->getAdapter()->commit();
		return array('status' => true);
    }
	
	/**
	 * Create an owner xmpp client instance of a group
	 * 
	 * @param	Dto_UserGroup	$groupDto
	 * @return	Xmpp
	 */
	protected function _factoryXmppClientFromGroup(Dto_UserGroup $groupDto)
	{
		$ownerUserDto = $this->userDao->fetchOne($groupDto->user_id);
        $client = XmppFactory::create($ownerUserDto);
		
		return $client;
	}
    
	/**
	 * Do subscrive user to group by xmpp service if the groups is not auto approved
	 * 
	 * @param	Dto_User 		$userDto
	 * @param	Dto_UserGroup	$groupDto
	 * @return	array 	Result array
	 */
    protected function _subscribe(Dto_User $userDto, Dto_UserGroup $groupDto)
    {
        // check if user already member of group
        if ($this->isMember($userDto, $groupDto)) {
            return array('status' => true);
        }
        
        // check if this user was invited by owner
        if ($this->isInvited($userDto, $groupDto)) {
            return $this->_updateAffiliation($userDto, $groupDto);
        }
        
        // check if request to join already sent
        if ($this->isPending($userDto, $groupDto)) {
            return array('status' => true);
        }
        
        // check if this user was invited by owner
        if ($this->isInvited($userDto, $groupDto)) {
            return $this->_updateAffiliation($userDto, $groupDto);
        }
        
        // do subscribe to node
        $client = XmppFactory::create($userDto);
        $response = $client->subscribeNode($groupDto->node_id);
            
        return array('status' => true);
    }
    
    /**
     * User unsubscribe from group
     * 
     * @param   Dto_UserGroup   $groupDto
     * @param   Dto_User        $userDto
     * @return  array
     */
    public function unsubscribe(Dto_UserGroup $groupDto, Dto_User $userDto)
    {
        $client = XmppFactory::create($userDto);
        $response = $client->unsubscribeNode($groupDto->node_id);
        
        if ($response->isFailure()) {
            return $this->_readXmppResponse($response);
        }
        
        // update the node member item
		$client = $this->_factoryXmppClientFromGroup($groupDto);
        $client->updateMember($groupDto->node_id);
        
        return array('status' => true);
    }
    
	/**
	 * Retrieve all subscribers of a group
	 * 
	 * @param	Dto_UserGroup	$groupDto
	 * @param	Qsoft_Db_Table_Rowset or Dto_User
	 */
    public function getGroupSubscribers(Dto_UserGroup $groupDto)
    {
        $subscribers = $this->chatSubscriptionDao->fetchSubscriberList($groupDto);
        $users = $this->userDao->fetchPublicBy('unique_id', $subscribers->toString('jid'));
        
        return $users;
    }
	
	/**
	 * Retrieve all user that has been invited but pending answer to group
	 * 
	 * @param	Dto_UserGroup	$groupDto
	 * @param	Qsoft_Db_Table_Rowset or Dto_User
	 */
    public function getGroupInvited(Dto_UserGroup $groupDto)
    {
        $invited = $this->groupInviteDao->fetchAllBy('node_id', $groupDto->node_id);
        $users = $this->userDao->fetchPublicBy('id', $invited->toString('user_id'));
        
        return $users;
    }
    
    /**
     * Retrieve all user that currently request to join group and waiting for approved
     * 
     * @param	Dto_UserGroup	$groupDto
	 * @param	Qsoft_Db_Table_Rowset or Dto_User
     */
    public function getGroupPending(Dto_UserGroup $groupDto)
    {
        $pending = $this->chatSubscriptionDao->fetchSubscriberList($groupDto, Xmpp::PUBSUB_NODE_PENDING);
        $users = $this->userDao->fetchPublicBy('unique_id', $pending->toString('jid'));
        
        return $users;
    }
    
	/**
	 * Retrieve all nodes that is subscribed by an user instance
	 * 
	 * @param	Dto_User	$userDto
	 * @return	Qsoft_Db_Table_Rowset
	 */
    public function getSubscribedNodes(Dto_User $userDto)
    {
        $chatJid = XmppFactory::createBareJid($userDto->getChatUsername());
        $subscribed = $this->chatSubscriptionDao->fetchSubscribed($chatJid);
        
        return $this->groupDao->fetchAllBy('node_id', $subscribed);
    }
    
    /**
     * Get all nodes that user created
     * 
     * @param	Dto_User	$userDto
	 * @return	Qsoft_Db_Table_Rowset of Dto_UserGroup
     */
    public function getCreatedNodes(Dto_User $userDto)
    {
        return $this->groupDao->fetchAllBy('user_id', $userDto->id);
    }
	
	/**
	 * Remove invitation record
	 * 
	 * @param   Dto_UserGroup   $groupDto
     * @param   Dto_User        $userDto
	 * @return	integer			The number of deleted records
	 */
	protected function deleteInvitation(Dto_UserGroup $groupDto, Dto_User $userDto)
	{
		return $this->groupInviteDao->deleteBy(array(
    		'node_id' => $groupDto->node_id,
    		'user_id' => $userDto->id,
		));
	}
    
}
