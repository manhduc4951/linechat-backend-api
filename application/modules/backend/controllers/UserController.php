<?php

class UserController extends Qsoft_Controller_Backend_Action
{
    
	protected $_businessClass = 'Business_User';
	
    protected $_daoClass = 'Dao_User';
    
    protected $_filterClass = 'Filter_User';
    
    protected $_formClass = 'Form_User';
	
	/**
     * Initialize method
     */
    public function init()
    {
        parent::init();
        
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/modules/backend/configs/navigation.ini', 'user_nav');
        $this->view->secondNavigation = new Zend_Navigation($config);
    }
    
    /**
     * Get the business model
     * 
     * @return Business_User
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
     * Update an existing record from modify action
     * 
     * @param mixed $itemDto
     * @param mixed $form
     * @param mixed $oldDto
     * @return array
     */
    protected function doUpdate($itemDto, $form, $oldDto)
    {        
        $itemDto->updated_at = Qsoft_Helper_Datetime::currentTime();
        $array_field_name = $this->getFormUpdateFields($form);
        $array_field_name[] = 'updated_at';
        
        $this->getDao()->update($itemDto, $array_field_name);
        return array('status' => true);        
    }
}