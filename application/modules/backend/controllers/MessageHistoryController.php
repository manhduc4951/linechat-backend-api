<?php

class MessageHistoryController extends Qsoft_Controller_Backend_Action
{ 
    protected $_daoClass = 'Dao_Chat_MessageHistory'; 
    
    protected $_filterClass = 'Filter_MessageHistory';    
	
	/**
     * Initialize method
     */
    public function init()
    {
        parent::init();
        
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/modules/backend/configs/navigation.ini', 'history_nav');
        $this->view->secondNavigation = new Zend_Navigation($config);
    }
	
	/**
     * Get the Dao object
     * 
     * @return Dao_MessageHistory
     */
	public function getDao()
	{
        return Dao_Chat_Factory::create($this->_daoClass);        
	}
    
}