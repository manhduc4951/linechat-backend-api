<?php

class UserTest extends PHPUnit_Framework_TestCase
{
	/**
	 * User business
	 * @var	Business_User
	 */
	protected $business;
    
    /**
     * @var Business_UserGroup
     */
    protected $groupBusiness;
	
	/**
	 * User Dao
	 * @var	Dao_User
	 */
	protected $dao;
    
    /**
     * @var Dao_UserGroup
     */
    protected $groupDao;
	
	public function setUp()
	{
		$this->business = new Business_User();
		$this->dao = new Dao_User();
        $this->groupBusiness = new Business_UserGroup();
        $this->groupDao = new Dao_UserGroup();
	}
	
	public function testCreateUser()
	{
		$avatarDao = new Dao_UserAvatar();
		$avatarDto = $avatarDao->fetchRow();
		
		$deviceId = Qsoft_Helper_String::random('alnum', 16);
		
		// try to create new user
		$userDto = new Dto_User();
		$userDto->avatar_id = $avatarDto->avatar_id;
		$result = $this->business->create($userDto, $deviceId);
		
		$this->assertTrue($result['status'], 'Cannot create new user with device id ' . $deviceId);
		
		// the user id is auto assigned with the right prefix
		$this->assertEquals(
			Business_User::USER_ID_PREFIX,
			substr($userDto->user_id, 0, strlen(Business_User::USER_ID_PREFIX)),
			'The auto generated user id not has right prefix (' . Business_User::USER_ID_PREFIX . ')'
		);
		
		return $userDto;
	}
	
	/**
	 * @depends testCreateUser
	 */
	public function testApiLogin(Dto_User $userDto)
	{
		// the login already done by create user service
		
		// test the last access must be in 5 seconds agos
		$dbUser = $this->dao->fetchOneBy('id', $userDto->id);
		$result = Qsoft_Helper_Datetime::getRange($dbUser->last_access);
		$this->assertTrue(abs($result) < 5, 'The last acess is more than 4 seconds agos after logged in');
		
		return $userDto;
	}
    
    /**
	 * @depends testApiLogin
	 */
    public function testUserCreateAndJoinGroup(Dto_User $userDto)
    {
        // create group
        $groupDto = new Dto_UserGroup();
		$groupDto->name = 'Unit test';
		$groupDto->description = 'This is for group unit test';
		$groupDto->is_auto_approve = 1;
        
        $result = $this->groupBusiness->create($groupDto, $userDto);
        $this->assertTrue($result['status'], 'Cannot create new group with name Unit test');
        
        // get my group
        $joinedGroups = $this->groupBusiness->getSubscribedNodes($userDto);
        $this->assertEquals(1, $joinedGroups->count(), 'The groups of user not be 1 after create new group');
        
        // fetch a random group to join
        $select = $this->groupDao->select()->order(new Zend_Db_Expr('RAND()'));
        $randomGroup = $this->groupDao->fetchRow($select);
        
        $result = $this->groupBusiness->subscribe($randomGroup, $userDto);
        $this->assertTrue($result['status'], 'Cannot subscribe a random group');
        
        // get my group
        $joinedGroups = $this->groupBusiness->getSubscribedNodes($userDto);
        $this->assertEquals(2, $joinedGroups->count(), 'The groups of user not be 2 after subscribe random group');
        
        return $userDto;
    }
	
	/**
	 * @depends testUserCreateAndJoinGroup
	 */
	public function testDeleteUser(Dto_User $userDto)
	{
		$result = $this->business->delete($userDto);
        $this->assertTrue($result['status'], 'Cannot delete user');
        
        // query user form backend database
        $testDto = $this->dao->fetchOne($userDto->id);
        $this->assertEquals('delete', $testDto->state, 'State of user is not deleted after delete');
        
        // query user from openfire database
        $select = $this->dao->getAdapter()
            ->select()
            ->from('ofUser')
            ->where('username = ?', $userDto->getChatUsername())
        ;
        $testDto = $this->dao->getAdapter()->fetchRow($select);
        $this->assertEmpty($testDto, 'Can query user by chat username from openfire database after delete');
        
        return $userDto;
	}
    
    /**
	 * @depends testDeleteUser
	 */
    public function testUserGroupAfterDelete(Dto_User $userDto)
    {
        $joinedGroups = $this->groupBusiness->getSubscribedNodes($userDto);
        $this->assertLessThan(1, $joinedGroups->count(), 'The groups of user '.
            'not be deleted and unsubscribed after delete user');
    }
	
	public function testCreateUserFailure()
	{
		$avatarDao = new Dao_UserAvatar();
		$avatarDto = $avatarDao->fetchRow();
		
		// create user with duplicate user (admin)
		$userDto = new Dto_User();
		$userDto->avatar_id = $avatarDto->avatar_id;
		$result = $this->business->create($userDto, 'admin');
		
		$this->assertFalse($result['status'], 'Can create new user with duplicate user (admin)');
		
		// error code must be unknown
		$this->assertEquals(
			ERROR_UNKNOWN_FROM_CHAT_SERVER,
			$result['error_code'],
			'The error code must be unknown error from chat server'
		);
	}
}