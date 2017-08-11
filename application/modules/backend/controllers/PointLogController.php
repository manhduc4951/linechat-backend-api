<?php

class PointLogController extends Qsoft_Controller_Backend_Action
{ 
    protected $_daoClass = 'Dao_PointLog';
    
    protected $_filterClass = 'Filter_PointLog';
    	
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
     * @return Dao_PointLog
     */
	public function getDao()
	{
		return parent::getDao();
	}
    
}