<?php

class Api_CallController extends App_Rest_Controller
{
    protected $_businessClass = 'Business_Call';
    
    protected $_daoClass = 'Dao_User';
	
	/**
     * Get the business model
     * 
     * @return Business_Call
     */
	public function getBusiness()
	{
		return parent::getBusiness();
	}
	
	/**
     * Get the Dao object
     * 
     * @return Dao_User
     */
	public function getDao()
	{
		return parent::getDao();
	}
    
    /**
     * Retrieve call settings from user action
     */
    public function settingsAction()
    {
        $uniqueId = $this->_getParam('unique_id');
        $userDto = (null == $uniqueId) ?
            Zend_Registry::get('api_user') : $this->getUser($uniqueId);
        
        $this->success(array('call_number_id' => $userDto->call_number_id));
    }
    
}
    