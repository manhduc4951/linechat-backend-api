<?php

class Api_GroupController extends App_Rest_Controller
{
    protected $_businessClass = 'Business_UserGroup';
    
    protected $_daoClass = 'Dao_UserGroup';
	
	/**
     * Get the business model
     * 
     * @return Business_UserGroup
     */
	public function getBusiness()
	{
		return parent::getBusiness();
	}
	
	/**
     * Get the Dao object
     * 
     * @return Dao_UserGroup
     */
	public function getDao()
	{
		return parent::getDao();
	}
    
    /**
     * Retrieve information of a group
     */
    public function getAction()
    {
        // retrieve the group to modify
        $groupDto = $this->getGroup();
        $userDto = Zend_Registry::get('api_user');
        
        $group = $groupDto->toEndUserArray();
        $subscribers = $this->getBusiness()->getGroupSubscribers($groupDto);
        $this->_userDao->addImageData($subscribers);
        $group['subscribers'] = $subscribers->toContactArray();
        
		$invited = $this->getBusiness()->getGroupInvited($groupDto);
		$this->_userDao->addImageData($invited);
		
		$pending = $this->getBusiness()->getGroupPending($groupDto);
		$this->_userDao->addImageData($pending);
		
		$group['invited'] = array_merge($invited->toContactArray(), $pending->toContactArray());
		
		
        $this->success(array('group' => $group));
    }
	
	/**
	 * Search list of groups action
	 */
	public function searchAction()
	{
		$groups = $this->getDao()->fetchPublic();
		
		$this->success(array('groups' => $groups->toEndUserArray()));
	}
    
    /**
     * Get all subscribed group
     */
    public function indexAction()
    {
        $userDto = Zend_Registry::get('api_user');
        $groups = $this->getBusiness()->getSubscribedNodes($userDto);
        
        $this->success(array('groups' => $groups->toEndUserArray()));
    }
	
	
    /**
     * Register group action
     */
    public function postAction()
    {
        $data = $this->getRequest()->getPost();
        $form = new Form_UserGroup;
        
        // validate the post parameters
        if ( ! $form->isValid($data)) {
            $errors = $form->getErrors();
            Zend_Registry::get('log')->log("register group failure: ". print_r($errors, true), Zend_Log::ERR);
            
            if (in_array(Zend_Validate_Db_Abstract::ERROR_NO_RECORD_FOUND, $errors['user_list'])) {
                $this->failure(ERROR_USER_NOT_FOUND);
            }
                       
            $this->failure(ERROR_FORM_VALIDATION_FAILURE);
        }
        
        // do create new group instance
        $groupDto = $form->mapFormToDto();
        $userDto = Zend_Registry::get('api_user');
		$invited = $this->getMultiUser($form->user_list->getValue());
        $result = $this->getBusiness()->create($groupDto, $userDto, $invited);
        
        if ($result['status'] !== true) {
            $form->rollback();
            return $this->fromArray($result);
        }
        
        $this->success(array('group' => $groupDto->toEndUserArray()));
    }
    
    /**
     * Update an group information
     */
    public function putAction()
    {
        // retrieve the group to modify
        $groupDto = $this->getGroup();
        
        // validate the submitted parameters
        $data = $this->getRequest()->getPost();
        $form = new Form_UserGroup($groupDto);
        
        // validate the post parameters
        if ( ! $form->isValid($data)) {
            $errors = $form->getErrors();
            Zend_Registry::get('log')->log("update group failure: ". print_r($errors, true), Zend_Log::ERR);
            if (in_array(Zend_Validate_Db_Abstract::ERROR_NO_RECORD_FOUND, $errors['user_list'])) {
                $this->failure(ERROR_USER_NOT_FOUND);
            }
            
            $this->failure(ERROR_FORM_VALIDATION_FAILURE);
        }
        
        // let business perform the action
        $oldImage = $groupDto->image;
        $userDto = Zend_Registry::get('api_user');
        $groupDto = $form->mapFormToDto($groupDto);
        
        // remove image field if user does not provide another one
        if (empty($groupDto->image)) {
            $groupDto->image = $oldImage;
        }
        
        // if user choose to delete the image
        if ($this->getRequest()->getPost('delete_image')) {
            $groupDto->image = '';
        }
        
		$invited = $this->getMultiUser($form->user_list->getValue());
		
        $result = $this->getBusiness()->update($groupDto, $userDto, $invited);
        
        if ($result['status'] !== true) {
            return $this->fromArray($result);
        }
        
        $this->success(array('group' => $groupDto->toEndUserArray()));
    }
    
    /**
     * Invite users to group
     */
    public function inviteAction()
    {
        // retrieve the group to modify
        $groupDto = $this->getGroup();
        
		// validate the list of users to invite
        $form = new Form_UserGroup($groupDto);
        
        if ( ! $form->user_list->isValid($this->getRequest()->getParam('user_list'))) {
            $this->failure(ERROR_USER_NOT_FOUND);
        }
        
		// invite user to group and response
        $userDto = Zend_Registry::get('api_user');
		$users = $this->getMultiUser($form->user_list->getValue());
        $result = $this->getBusiness()->invite($users, $groupDto, $userDto);
		
        $this->fromArray($result);
    }
	
	/**
	 * Owner of group accept user to join group if have request
	 */
	public function joinAcceptAction()
	{
		$groupDto = $this->getGroup();
		$userDto = Zend_Registry::get('api_user');
		$members = $this->getMultiUser($this->_getParam('user_list'));
		
		$result = $this->getBusiness()->acceptJoinGroup($members, $groupDto, $userDto);
		$this->fromArray($result);
	}
    
    /**
     * Owner of group reject user to join group if have request
     */
    public function joinRejectAction()
    {
        $groupDto = $this->getGroup();
        $userDto = Zend_Registry::get('api_user');
        $uniqueId = $this->_getParam('unique_id', '');
        if ( ! $memberDto = $this->_userDao->fetchOnePublicBy('unique_id', $uniqueId)) {
            $this->failure(ERROR_USER_NOT_FOUND);
        }
        
        $subid = $this->_getParam('subid');
        
        $result = $this->getBusiness()->rejectJoinGroup($memberDto, $subid, $groupDto, $userDto);
        $this->fromArray($result);
    }
	
	/**
	 * User reject an invitation to join group
	 */
	public function inviteRejectAction()
	{
		$groupDto = $this->getGroup();
		$userDto = Zend_Registry::get('api_user');
		
		$result = $this->getBusiness()->rejectInvitation($groupDto, $userDto);
		$this->fromArray($result);
	}
    
    /**
     * Delete group action
     */
    public function deleteAction()
    {
        $groupDto = $this->getGroup();
        $userDto = Zend_Registry::get('api_user');
        $this->fromArray($this->getBusiness()->delete($groupDto, $userDto));
    }
    
    /**
     * User want to join group action
     */
    public function joinAction()
    {
        $groupDto = $this->getGroup();
        $userDto = Zend_Registry::get('api_user');
        $this->fromArray($this->getBusiness()->subscribe($groupDto, $userDto));
    }
    
    /**
     * User want to leave group action
     */
    public function leaveAction()
    {
        $groupDto = $this->getGroup();
        $userDto = Zend_Registry::get('api_user');
        $this->fromArray($this->getBusiness()->unsubscribe($groupDto, $userDto));
    }
    
    /**
     * Get the group from request id
     * If not found, throw 404 error page instead
     * 
     * @return  Dto_UserGroup
     */
    protected function getGroup()
    {
        $id = $this->_getParam('id', '');
        $groupDto = $this->getDao()->fetchOnePublicBy('node_id', $id) OR $this->failure(ERROR_GROUP_NOT_FOUND);
        
        return $groupDto;
    }
    
}