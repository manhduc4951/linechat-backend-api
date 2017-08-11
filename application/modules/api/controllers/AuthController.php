<?php

class Api_AuthController extends Qsoft_Rest_Controller
{
    protected $_daoClass = 'Dao_User';
    
    protected $_businessClass = 'Business_User';
    
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
     * Login action
     */
    public function indexAction()
    {
        $data = $this->getRequest()->getPost();
        $form = new Form_ApiLogin;

        // validate the post parameters
        if ( ! $form->isValid($data)) {
            Zend_Registry::get('log')->log("api login failure: ". print_r($form->getErrors(), true), Zend_Log::ERR);
            $this->failure(ERROR_FORM_VALIDATION_FAILURE);
        }
        
        // fetch the api user with provided identity
        $uniqueId = trim($form->getValue('unique_id'));
        $userDto = $this->getDao()->fetchOnePublicBy('unique_id', $uniqueId);
        
        if ( ! $userDto) {
            $this->failure(ERROR_AUTHENTICATE_FAILURE);
        }
        
        // do login business
        $result = $this->getBusiness()->apiLogin($userDto, $form->getValue('longitude'), $form->getValue('latitude'));
        
        if ($result['status'] !== true) {
            return $this->fromArray($result);
        }
        
        $this->success(array('user' => $userDto->toEndUserArray()));
    }
    
    /**
     * Change device action
     */
    public function changeDeviceAction()
    {
        // forward to get change device code action for another method but post 
        if ( ! $this->getRequest()->isPost()) {
            //return $this->forward('get-change-device-code');
            return $this->badRequestAction();
        }
        
        $deviceCode = $this->getRequest()->getPost('confirmation_code', '');
        $deviceId = $this->getRequest()->getPost('device_id', '');
        
        // validate the device id
        $apiRegisterForm = new Form_ApiRegister;
        $validator = $apiRegisterForm->device_id;
        
        if ( ! $validator->isValid($deviceId)) {
            $errors = $validator->getErrors();
            
            if (count($errors) > 1 OR ! in_array('recordFound', $errors)) {
                $this->failure(ERROR_FORM_VALIDATION_FAILURE);
            }
        }
        
        $userDto = $this->getDao()->fetchOnePublicBy('unique_id', $deviceCode);
        $result = $this->getBusiness()->changeDevice($userDto, $deviceId);
        
        if (true !== $result['status']) {
            $this->fromArray($result);
        }
        
        $this->success(array('user' => $userDto->toEndUserArray()));
    }
    
    /**
     * Get changde device code action
     * dropped
     */
    public function getChangeDeviceCodeAction()
    {
        $userDto = Zend_Registry::get('api_user');
        $result = $this->getBusiness()->generateChangeDeviceCode($userDto);
        
        if (true !== $result['status']) {
            $this->fromArray($result);
        }
        
        $this->success(array('confirmation_code' => $userDto->change_device_code));
    }
    
    /**
     * Logout action
     */
    public function logoutAction()
    {
        $userDto = Zend_Registry::get('api_user');
        $this->getBusiness()->apiLogout($userDto);
        
        $this->success();
    }
    
    /**
     * Api deny action
     * 
     * @return void
     */
    public function denyAction()
    {
        $this->failure(ERROR_ACCESS_DENIED);
    }
    
}
