<?php

class UserImageController extends Qsoft_Controller_Backend_Action
{
    
	protected $_businessClass = 'Business_Image';
	
    protected $_daoClass = 'Dao_ImageStatus';
    
    protected $_filterClass = 'Filter_ImageStatus';
	
	/**
     * Initialize method
     */
    public function init()
    {
        parent::init();
        
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/modules/backend/configs/navigation.ini', 'manage_nav');
        $this->view->secondNavigation = new Zend_Navigation($config);
    }
    
    /**
     * Get the business model
     * 
     * @return Business_Image
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
            $blockImage = $this->getBusiness()->blockImage($this->_request->getPost('delete_image', array()));
            if($blockImage['number'] > 0) {
                $this->noticeMessage('Image has been blocked successfully.');    
            }    
        }
        
        parent::indexAction();
    }
}