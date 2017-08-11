<?php

class UserGroupTest extends PHPUnit_Framework_TestCase
{
	/**
	 * User group business
	 * @var	Business_UserGroup
	 */
	protected $business;
	
	/**
	 * User group Dao
	 * @var	Dao_UserGroup
	 */
	protected $dao;
	
	/**
	 * User Dao
	 * @var	Dao_User
	 */
	protected $userDao;
	
	/**
	 * User Dto
	 * @var Dto_User
	 */
	protected $userDto;
	
	/**
	 * User Dto member
	 * @var Dto_User
	 */
	protected $memberDto;
    
    /**
     * User Dto member
     * @var Dto_User
     */
    protected $memberDto2;
	
	public function setUp()
	{
		$this->business = new Business_UserGroup();
		$this->dao = new Dao_UserGroup();
		$this->userDao = new Dao_User();
		$this->userDto = $this->userDao->fetchOneBy('unique_id', 'phpapp01');
		$this->memberDto = $this->userDao->fetchOneBy('unique_id', 'phpapp02');
        $this->memberDto2 = $this->userDao->fetchOneBy('unique_id', 'phpapp03');
	}
	
	public function testCreateGroup()
	{
		$groupDto = new Dto_UserGroup();
		$groupDto->name = 'Unit test';
		$groupDto->description = 'This is for group unit test';
		$groupDto->is_auto_approve = 1;
		
		$result = $this->business->create($groupDto, $this->userDto);
		
		$this->assertTrue($result['status'], 'Cannot create new group with name Unit test');
		
		$this->assertTrue( ! empty($groupDto->id), 'Group dto has no id after created');
		$this->assertGreaterThan(0, $groupDto->id, 'Group dto id has no greater than 0 after created');
		
		return $groupDto;
	}
	
	/**
     * @depends testCreateGroup
     * Group contain 1 members: memberDto
     */
	public function testSubscibeGroup(Dto_UserGroup $groupDto, $withAutoApprove = true, Dto_User $userDto = null)
	{
		if (null === $userDto) {
			$userDto = $this->memberDto;
		}
		
		$result = $this->business->subscribe($groupDto, $userDto);
		$this->assertTrue($result['status'], 'Subscribe group return failure');
		
		$uniqueId = $this->_getSubsribedUniqueIdArray($groupDto);
		
		if ($withAutoApprove) {
			$this->assertContains($userDto->unique_id, $uniqueId, 'User unique id not in subsribed array of group');
            
            // check is member by business function
            $isMember = $this->business->isMember($userDto, $groupDto);
            $this->assertTrue($isMember, 'Business said that user is not member after subsribe success');
		} else {
			$this->assertNotContains($userDto->unique_id, $uniqueId,
				'User unique id in subsribed array of group but owner not confirm yet');
            
            // check user is pending
            $isPending = $this->business->isPending($userDto, $groupDto);
            $this->assertTrue($isPending, 'User is not pending after subscribe in not auto approve group');
            
            // continous request to join with pending state
            $result = $this->business->subscribe($groupDto, $userDto);
            $this->assertTrue($result['status'], 'ReSubscribe group with pending state return failure');
		}
		
		return $groupDto;
	}

    /**
     * @depends testSubscibeGroup
     * Group contain 1 members: memberDto
     */
    public function testReSubscribeGroup(Dto_UserGroup $groupDto)
    {
        $result = $this->business->subscribe($groupDto, $this->memberDto);
        $this->assertTrue($result['status'], 'Subscribe group success evenif user alread a member of group');
        
        // total members will not be changed
        $total = count($this->business->getGroupSubscribers($groupDto));
        $this->assertEquals(2, $total, 'Total members of group is ' . $total . ' but 2 expected');
        
        return $groupDto;
    }
    
    /**
     * @depends testReSubscribeGroup
     * Group contain 1 members: memberDto
     */
    public function testGetSubscribedNodes(Dto_UserGroup $groupDto)
    {
        $subscribedRowset = $this->business->getSubscribedNodes($this->memberDto);
        $this->assertGreaterThan(0, count($subscribedRowset), 'User have less than 1 group after join new group');
        
        if ($group = end($subscribedRowset)) {
            $this->assertEquals($group->id, $groupDto->id, 'The end subscribed group of user is not expected group');
        }
        
        return $groupDto;
    }
	
	/**
     * @depends testGetSubscribedNodes
     * Group contain 0 members:
     */
	public function testUnsubscribeGroup(Dto_UserGroup $groupDto)
	{
		$result = $this->business->unsubscribe($groupDto, $this->memberDto);
		$this->assertTrue($result['status'], 'Un subscribe group return failure');
		
		$uniqueId = $this->_getSubsribedUniqueIdArray($groupDto);
		$this->assertFalse(in_array($this->memberDto->unique_id, $uniqueId),
			'User unique id in subsribed array of group but user just left');
		
		return $groupDto;
	}
	
	/**
     * @depends testUnsubscribeGroup
     * Group contain 0 members
     */
	public function testUpdateGroup(Dto_UserGroup $groupDto)
	{
		$newname = 'Unit test edit 1st';
		// do update group
		$groupDto->name = $newname;
		$groupDto->is_auto_approve = 0;
		$result = $this->business->update($groupDto, $this->userDto);
		$this->assertTrue($result['status'], 'Cannot update group');
		
		// test the backend database change
		$updatedGroup = $this->dao->fetchOne($groupDto->id);
		$this->assertEquals($updatedGroup->name, $newname, 'The group name cannot change after update');
		
		// test the openfire database change
		
		return $groupDto;
	}
	
	/**
     * @depends testUpdateGroup
     * Group contain 0 members
     */
	public function testSubscribeGroupWithNotAutoApprove(Dto_UserGroup $groupDto)
	{
		return $this->testSubscibeGroup($groupDto, false, $this->memberDto2);
	}
	
	/**
     * @depends testSubscribeGroupWithNotAutoApprove
     * Group contain 1 members: memberDto2
     */
	public function testOwnerAcceptJoinGroup(Dto_UserGroup $groupDto)
	{
	    // test non-owner accept to join group
	    $result = $this->business->acceptJoinGroup($this->memberDto2, $groupDto, $this->memberDto);
        $this->assertFalse($result['status'], 'Non-owner can confirm user to join group');
        
        // test join group with empty members
        $result = $this->business->acceptJoinGroup(null, $groupDto, $this->userDto);
        $this->assertTrue($result['status'], 'Confirm to join group with empty array but failure');
        
        // test accept with user not request to join group
        // $userDto = $this->userDao->fetchOneBy('unique_id', 'newnewnew1');
        // $result = $this->business->acceptJoinGroup($userDto, $groupDto, $this->userDto);
        // $this->assertFalse($result['status'], 'Owner can confirm user not request to join group');
        
        // Owner accept success
		$result = $this->business->acceptJoinGroup($this->memberDto2, $groupDto, $this->userDto);
		$this->assertTrue($result['status'], 'Owner cannot confirm user to join group');
        
        // test accept to join with already member
        $result = $this->business->acceptJoinGroup($this->memberDto2, $groupDto, $this->userDto);
        $this->assertTrue($result['status'], 'Owner cannot accept to join with already member');
        
        // total members still 2
        $total = count($this->business->getGroupSubscribers($groupDto));
        $this->assertEquals(2, $total, 'Total members of group is ' . $total . ' but 2 expected');
		
		return $groupDto;
	}
    
    /**
     * @depends testOwnerAcceptJoinGroup
     * Group contain 1 members: memberDto2
     */
    public function testOwnerRejectJoinGroup(Dto_UserGroup $groupDto)
    {
        // request to join
        $result = $this->business->subscribe($groupDto, $this->memberDto);
        $this->assertTrue($result['status'], 'User cannot request to join group');
        
        // check status is pending
        $result = $this->business->isPending($this->memberDto, $groupDto);
        $this->assertTrue($result, 'State of user is not pending after request');
        
        // get the sub id
        $chatSubDao = Dao_Chat_Factory::create('Dao_Chat_PubsubSupscription');
        $dto = $chatSubDao->fetchOneBy(array(
            'nodeID' => $groupDto->node_id,
            'jid' => XmppFactory::createBareJid($this->memberDto->getChatUsername()),
        ));
        
        // owner reject request
        $result = $this->business->rejectJoinGroup($this->memberDto, $dto->id, $groupDto, $this->userDto);
        $this->assertTrue($result['status'], 'Owner cannot rejct user to join group');
        
        // check status is pending
        $result = $this->business->isPending($this->memberDto, $groupDto);
        $this->assertFalse($result, 'State of user is pending after owner reject');
        
        // total members still 2
        $total = count($this->business->getGroupSubscribers($groupDto));
        $this->assertEquals(2, $total, 'Total members of group is ' . $total . ' but 2 expected');
        
        return $groupDto;
    }
    
    /**
     * @depends testOwnerRejectJoinGroup
     * Group contain 1 members: memberDto2
     */
    public function testInviteToGroup(Dto_UserGroup $groupDto)
    {
        // invite already member
        $result = $this->business->invite($this->memberDto2, $groupDto, $this->userDto);
        $this->assertTrue($result['status'], 'Owner cannot invite already member');
        
        // but above invite does not effect
        $result = $this->business->isInvited($this->memberDto2, $groupDto);
        $this->assertFalse($result, 'The invitation to already member still affect');
        
        // invite success
        $result = $this->business->invite($this->memberDto, $groupDto, $this->userDto);
        $this->assertTrue($result['status'], 'Owner cannot invite user to join group');
        
        // user must be appearance in invited list
        $invitedRowset = $this->business->getGroupInvited($groupDto);
        $memberDto = $this->memberDto;
        $filtered = array_filter($invitedRowset->toArray(), function ($var) use ($memberDto) {
            return ($var['unique_id'] == $memberDto->unique_id);
        });
        
        $this->assertNotEmpty($filtered, 'User does not apprearance in invited list after invite');
        
        // check user is invited in group by business
        $isInvited = $this->business->isInvited($this->memberDto, $groupDto);
        $this->assertTrue($isInvited, 'Business said that user is not invited after invite');
        
        // total members
        $total = count($this->business->getGroupSubscribers($groupDto));
        $this->assertEquals(2, $total, 'Total members of group is ' . $total . ' but 2 expected');
        
        return $groupDto;
    }
    
    /**
     * @depends testInviteToGroup
     * Group contain 1 members: memberDto2
     */
    public function testRejectInvitation(Dto_UserGroup $groupDto)
    {
        $result = $this->business->rejectInvitation($groupDto, $this->memberDto);
        $this->assertTrue($result['status'], 'User cannot reject invitation to join group');
        
        // check user is not invited in group by business
        $isInvited = $this->business->isInvited($this->memberDto, $groupDto);
        $this->assertFalse($isInvited, 'Business said that user invited after reject invitation');
        
        // check is not member by business function
        $isMember = $this->business->isMember($this->memberDto, $groupDto);
        $this->assertFalse($isMember, 'Business said that user is member after reject invitation');
        
        return $groupDto;
    }
	
	/**
     * @depends testRejectInvitation
     */
	public function testDeleteGroup(Dto_UserGroup $groupDto)
	{
		$result = $this->business->delete($groupDto, $this->userDto);
		$this->assertTrue($result['status'], 'Cannot delete new group with name Unit test');
		
		// try to query group in database
		$groupDto = $this->dao->fetchOne($groupDto->id);
		$this->assertEmpty($groupDto, 'Group dto still exist in database after deleted');
	}
	
	protected function _getSubsribedUniqueIdArray(Dto_UserGroup $groupDto)
	{
		$subscribed = $this->business->getGroupSubscribers($groupDto);
		return $subscribed->toString('unique_id');
	}
	
}
