<?php

class ImageController extends Qsoft_Controller_Backend_Action
{
    
	protected $_businessClass = 'Business_User';
	
    protected $_daoClass = 'Dao_User';
    
    protected $_filterClass = 'Filter_Image';
	
	/**
     * Initialize method
     */
    public function init()
    {
        parent::init();
        
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/modules/backend/configs/navigation.ini', 'image_nav');
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
    
    public function indexAction()
    {
        if ($this->_request->isPost()) {           
            $this->getDao()->dontDisplayImage($this->_request->getPost('delete_image', array()));
            $this->noticeMessage("Your selected items has been hide already");
        }
        
        parent::indexAction();
    }
}