<?php

class StampController extends Qsoft_Controller_Backend_Action
{ 
	
    protected $_daoClass = 'Dao_Stamp';
    
    protected $_formClass = 'Form_Stamp';
    
    protected $_businessClass = 'Business_Stamp';
	
	/**
     * Initialize method
     */
    public function init()
    {
        parent::init();
        
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/modules/backend/configs/navigation.ini', 'settings_nav');
        $this->view->secondNavigation = new Zend_Navigation($config);
    }
    	
	/**
     * Get the Dao object
     * 
     * @return Dao_Stamp
     */
	public function getDao()
	{
		return parent::getDao();
	}
    
    /**
     * Get the Business object
     * 
     * @return Business_Stamp
     */
    public function getBusiness()
    {
        return parent::getBusiness();
    }
    
    
    /**
     * Listing all record
     */
    public function indexAction()
    { 
        
        $this->processForm();
        $this->view->form->setAttrib('class', 'index-form');
        parent::indexAction();
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
        if($this->getBusiness()->checkFileUpload($itemDto) > 0) {            
            return array('status' => false, 'error_code' => ERROR_ZIP_FILE_IS_EMPTY);    
        } else {
            $array_field_name = $this->getFormUpdateFields($form);            
            $result = $this->getBusiness()->updateStamp($itemDto, $form, $oldDto, $array_field_name);
            return array('status' => $result['status']);
        }
    }
    
    /**
     * Insert new record from modify action
     * 
     * @param mixed $itemDto
     * @param mixed $form
     * @return array
     */
    protected function doInsert($itemDto, $form)
    {
       $result = $this->getBusiness()->createStamp($itemDto, $form);
       return array('status' => $result['status']);
    }
    
    
}
