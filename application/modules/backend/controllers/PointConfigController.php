<?php

class PointConfigController extends Qsoft_Controller_Backend_Action
{ 
    protected $_daoClass = 'Dao_PointConfig';
    
    protected $_formClass = 'Form_PointConfig';    
    
    
    	
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
     * @return Dao_PointLog
     */
	public function getDao()
	{
		return parent::getDao();
	}
    
    public function indexAction()
    {        
        $item = $this->getDao()->fetchRow();

        $form = $this->createForm($item);
        
        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getPost())) {
                $itemDto = $form->mapFormToDto($item);
                if ($item) {
                    $result = $this->doUpdate($itemDto, $form, $item);
                } else {
                    $result = $this->doInsert($itemDto, $form, $item);
                }

                if ($result['status'] === true) {
                    
                    $this->flashNoticeMessage('Item has been saved successfully.');
                    
                    if ( ! $id) {
                        $id = $result['primary'];
                    }
                    
                    $this->_redirect($this->_request->getControllerName());
                } else {
                    $this->warningMessage('error_' . $result['error_code']);
                }
            }
        }
        
        $this->view->form = $form;  
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
        
        $this->getDao()->update($itemDto, $this->$array_field_name);
        return array('status' => true);        
    }
    
}