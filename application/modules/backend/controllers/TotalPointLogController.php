<?php

class TotalPointLogController extends Qsoft_Controller_Backend_Action
{ 
    protected $_daoClass = 'Dao_TotalPointLog';     
    
    protected $_filterClass = 'Filter_TotalPointLog';    
	
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
    
    public function indexAction()
    {
        $daoUserAvatar = new Dao_UserAvatar();        
        $this->view->avatars = $daoUserAvatar->fetchAll();
        parent::indexAction();
        
    }
}