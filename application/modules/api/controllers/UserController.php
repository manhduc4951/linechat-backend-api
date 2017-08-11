<?php

class Api_UserController extends App_Rest_Controller
{

    protected $_businessClass = 'Business_User';
    protected $_daoClass = 'Dao_User';
    
    /**
     * Get the dbtable
     * 
     * @return Dao_User
     */
    protected function getDao()
    {
        return parent::getDao();
    }
    
    /**
     * Get the business model
     * 
     * @return Business_User
     */
    protected function getBusiness()
    {
        return parent::getBusiness();
    }

    /**
     * Pre register action
     */
    public function preRegisterAction()
    {
        $deviceId = $this->getRequest()->getPost('device_id', false);
        $form = new Form_ApiRegister;

        if (!$form->device_id->isValid($deviceId)) {
            if (in_array(Zend_Validate_Db_Abstract::ERROR_RECORD_FOUND, $form->device_id->getErrors())) {
                $this->failure(ERROR_UNIQUE_ID_DUPLICATE);
            }

            $this->failure(ERROR_FORM_VALIDATION_FAILURE);
        }

        $userDto = new Dto_User();
        $this->getBusiness()
            ->generateUniqueId($userDto, $deviceId)
            ->generateNickName($userDto)
            ->generateUserId($userDto, '');

        $this->success($userDto->toArray(array('nick_name', 'user_id')));
    }

    /**
     * Register action
     */
    public function postAction()
    {
        $data = $this->getRequest()->getPost();
        $form = new Form_ApiRegister;

        // validate the post parameters
        if (!$form->isValid($data)) {
            $errors = $form->getErrors();
            Zend_Registry::get('log')->log("register user failure: ". print_r($errors, true), Zend_Log::ERR);
            
            if (in_array(Zend_Validate_Db_Abstract::ERROR_RECORD_FOUND, $errors['device_id'])) {
                $this->failure(ERROR_UNIQUE_ID_DUPLICATE);
            }

            if (in_array(Zend_Validate_Db_Abstract::ERROR_RECORD_FOUND, $errors['user_id'])) {
                $this->failure(ERROR_USER_ID_DUPLICATE);
            }

            $this->failure(ERROR_FORM_VALIDATION_FAILURE);
        }

        $userDto = $form->mapFormToDto();
        $deviceId = $form->device_id->getValue();
        $result = $this->getBusiness()->create($userDto, $deviceId);

        if (true !== $result['status']) {
            $this->fromArray($result);
        }

        $this->success(array('user' => $userDto->toEndUserArray()));
    }

    /**
     * List users action
     */
    public function indexAction()
    {
        if ($this->_getParam('distance')) {
            $count = Zend_Registry::get('app_config')->user->search->distance->itemPerPage;
        } else {
            $count = Zend_Registry::get('app_config')->user->search->itemPerPage;
        }
        
        $userDto = Zend_Registry::get('api_user');
        $users = $this->getDao()->search($this->getRequest()->getQuery(), $userDto, null, $count);
        
        $this->getDao()->addImageData($users);
        $this->success(array('users' => $users->toContactArray()));
    }
    
    /**
     * Find an user by user id
     * 
     * @return void
     */
    public function findAction()
    {
        $userId = trim($this->_getParam('user_id', ''));
        $user = $this->getDao()->fetchOnePublicBy('user_id', $userId);
        
        if ($user AND $user->isAllowToFindById()) {
            $result['user'] = $user->toContactArray();
            $this->success($result);
        } 
        
        $this->failure(ERROR_USER_NOT_FOUND);
    }

    /**
     * Get User Profile
     */
    public function getAction()
    {
        $uniqueId = $this->_getParam('id', '');
        
        if ($uniqueId == 'profile') {
            // Get current Dto_User by token
            $userDto = Zend_Registry::get('api_user');
        } else {
            // Get user profile by provided id
            $userDto = $this->getDao()->fetchOnePublicBy('unique_id', $uniqueId);
        }
        
        if ( ! $userDto) {
            $this->failure(ERROR_USER_NOT_FOUND);
        }
        
        $this->success(array('profile' => $userDto->toProfileArray()));
    }

    /**
     * Update user profile information
     */
    public function putAction()
    {
        // Get current Dto_User by token
        $userDto = Zend_Registry::get('api_user');
        $userForm = new Form_ApiRegister;
        $fieldName = $this->_getParam('name');
        $fieldValue = $this->_getParam('value');

        switch ($fieldName) {
            // update user emoticon
            case 'emoticon_id':
                $fieldValue = empty($fieldValue) ? null : $fieldValue;
            // update user description
            case 'description':
            // update user avatar
            case 'avatar_id':
            // update user id
            case 'user_id':
            // update user name
            case 'nick_name':
                if (!$userForm->{$fieldName}->isValid($fieldValue)) {
                    $errors = $userForm->{$fieldName}->getErrors();
                    Zend_Registry::get('log')->log("update user info failure with {$fieldName}: ". print_r($errors, true), Zend_Log::ERR);
                    
                    if ($fieldName == 'user_id' AND
                        in_array(Zend_Validate_Db_Abstract::ERROR_RECORD_FOUND, $errors))
                    {
                        $this->failure(ERROR_USER_ID_DUPLICATE);
                    }
                    
                    $this->failure(ERROR_FORM_VALIDATION_FAILURE);
                }
                break;

            // default action if the field name is not allowed to modify or not exist
            default:
                $this->failure(ERROR_BAD_REQUEST);
        }

        $result = $this->getBusiness()->update($userDto, $fieldName, $fieldValue);
        $this->fromArray($result);
    }
    
    /**
     * Delete user for re-register
     * @see Qsoft_Rest_Controller::deleteAction()
     */
    public function deleteAction()
    {
        $userDto = Zend_Registry::get('api_user');
        $result = $this->getBusiness()->delete($userDto);
        
        $this->fromArray($result);
    }

	/**
	 * Delete user action
	 * TODO: remove
	 */
	public function removeAction()
	{
		$userDto = Zend_Registry::get('api_user');
		$fileTransferDao = new Dao_FileTransfer();
		$fileTransferDao->deleteBy('user_id', $userDto->id);
        $appStartLogDao = new Dao_AppStartLog();
        $appStartLogDao->deleteBy('user_id', $userDto->id);
		$groupDao = new Dao_UserGroup();
		$groupDao->deleteBy('user_id', $userDto->id);
		$this->getDao()->delete($userDto);
		
		$this->success();
	}

    /**
     * Update user image 
     */
    public function imageAction()
    {
        $userDto = Zend_Registry::get('api_user');
        
        if ($this->getRequest()->isDelete()) {
            // delete the current image
            $result = $this->getBusiness()->deleteImage($userDto);
            return $this->fromArray($result);
        }
        
        $userForm = new Form_UploadImage;
        $fieldValue = $this->_getParam('image');

        if (!$userForm->image->isValid($fieldValue)) {
            Zend_Registry::get('log')->log("upload user image failure: ". print_r($userForm->image->getErrors(), true), Zend_Log::ERR);
            $this->failure(ERROR_FORM_VALIDATION_FAILURE);
        }
        $imageName = $userForm->image->getValue();
        $result = $this->getBusiness()->updateImage($userDto, $imageName);
        if (true !== $result['status']) {
            $this->fromArray($result);
        }

        $this->success(array('image' => $userDto->toImageUrlArray()));
    }
    
    /**
     * Get privacy setings of user
     */
    public function getPrivacyAction()
    {
        if ($this->getRequest()->isPost()) {
            return $this->forward('post-privacy');
        }
        
        $userDto = Zend_Registry::get('api_user');
        $this->success(array('privacy' => $userDto->toPrivacySettingsArray()));
    }
    
    /**
     * Update user privacy settings
     */
    public function postPrivacyAction()
    {
        $userDto = Zend_Registry::get('api_user');
        $form = new Form_UserPrivacy($userDto);
        
        if ( ! $form->isValid($this->getRequest()->getPost())) {
            $this->failure(ERROR_FORM_VALIDATION_FAILURE);
        }
        
        $userDto = $form->mapFormToDto($userDto);
        $this->getDao()->update($userDto, $this->getFormUpdateFields($form));
        $this->success();
    }
    
    /**
     * Get notification setings of user
     */
    public function getNotificationAction()
    {
        if ($this->getRequest()->isPost()) {
            return $this->forward('post-notification');
        }
        
        $userDto = Zend_Registry::get('api_user');
        $this->success(array('settings' => $userDto->toNotificationSettingsArray()));
    }
    
    /**
     * Update user notification settings
     */
    public function postNotificationAction()
    {
        $userDto = Zend_Registry::get('api_user');
        $form = new Form_UserNotification($userDto);
        
        if ( ! $form->isValid($this->getRequest()->getPost())) {
            $this->failure(ERROR_FORM_VALIDATION_FAILURE);
        }
        
        $userDto = $form->mapFormToDto($userDto);
        $this->getDao()->update($userDto, $this->getFormUpdateFields($form));
        $this->success();
    }
    
    /**
     * User retrieve public home settings
     */
    public function getPublicHomeSettingAction()
    {
        if ($this->getRequest()->isPost()) {
            return $this->forward('post-public-home-setting');
        }
        
        $userDto = Zend_Registry::get('api_user');
        $settings = $this->getBusiness()->getPublicHomeSetting($userDto);
        $this->_userDao->addImageData($settings);
        $this->success(array('settings' => $settings->toContactArray()));
    }
    
    /**
     * User update public home settings
     */
    public function postPublicHomeSettingAction()
    {
        $users = $this->getMultiUser($this->_getParam('user_list'));
        $userDto = Zend_Registry::get('api_user');
        $result = $this->getBusiness()->updatePublicHomeSetting($userDto, $users);
        
        $this->fromArray($result);
    }
    
    /**
     * User retrieve hide settings
     */
    public function getHideSettingAction()
    {
        if ($this->getRequest()->isPost()) {
            return $this->forward('post-hide-setting');
        }
    
        $userDto = Zend_Registry::get('api_user');
        $settings = $this->getBusiness()->getHideSetting($userDto);
        $this->_userDao->addImageData($settings);
        $this->success(array('settings' => $settings->toContactArray()));
    }
    
    /**
     * User update hidesettings
     */
    public function postHideSettingAction()
    {
        $users = $this->getMultiUser($this->_getParam('user_list'));
        $userDto = Zend_Registry::get('api_user');
        $result = $this->getBusiness()->updateHideSetting($userDto, $users);
    
        $this->fromArray($result);
    }
    
    /**
     * Check current user is blocked by another user action
     */
    public function isBlockedByAction()
    {
        $userDto = Zend_Registry::get('api_user');
        $targetUserDto = $this->getUser($this->_getParam('unique_id', 0));
        
        $blockedState = $this->getBusiness()->isBlockedBy($userDto, $targetUserDto);
        $this->success(array('blocked' => (int) $blockedState));
    }
    
    /**
     * Get all synchronize data
     * Use when user change device or re-intall application
     */
    public function syncAction()
    {
    	$userDto = Zend_Registry::get('api_user'); /* @var $userDto Dto_User */
    	$syncData = array();
    	
    	// collect settings data
    	$syncData['privacy'] = $userDto->toPrivacySettingsArray();
    	$syncData['notification'] = $userDto->toNotificationSettingsArray();
    	
    	// hide settings
    	$hideSettings = $this->getBusiness()->getHideSetting($userDto);
    	$this->getDao()->addImageData($hideSettings);
    	$syncData['hide'] = $hideSettings->toContactArray();
    	
    	// block home settings
    	$homeSettings = $this->getBusiness()->getPublicHomeSetting($userDto);
    	$this->getDao()->addImageData($homeSettings);
    	$syncData['public_home'] = $homeSettings->toContactArray();
    	
    	// stamps list
    	$stampDao = new Dao_Stamp();
    	$stamps = $stampDao->fetchAllPublicWithPurchaseStatus($userDto->id);
    	$syncData['stamps'] = $stamps->toEndUserArray();
    	
    	// all friend images
    	$friends = $this->getBusiness()->getFriendList($userDto);
    	$this->getDao()->addImageData($friends);
    	$syncData['friends'] = $friends->toContactArray();
    	
    	return $this->success($syncData);
    }

}
