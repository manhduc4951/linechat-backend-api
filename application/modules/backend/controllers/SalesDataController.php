<?php

class SalesDataController extends Qsoft_Controller_Backend_Action
{ 
    protected $_daoClass = 'Dao_SalesData';     
    
    protected $_filterClass = 'Filter_SalesData';    
	
	/**
     * Initialize method
     */
    public function init()
    {
        parent::init();
        
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/modules/backend/configs/navigation.ini', 'summary_nav');
        $this->view->secondNavigation = new Zend_Navigation($config);
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
    
    
}