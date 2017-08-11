<?php

class WordNotGoodController extends Qsoft_Controller_Backend_Action
{ 
    protected $_daoClass = 'Dao_WordNotGood'; 
    
    protected $_formClass = 'Form_WordNotGood';
	
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
     * @return Dao_User
     */
	public function getDao()
	{
		return parent::getDao();
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
     * Delete a word not good
     * 
     * @return void
     */
    public function deleteAction()
    {
        $this->getHelper('viewRenderer')->setNoRender();
        
        $id = $this->_request->getParam('id', 0);
        $wordNotGoodDto = $this->getDao()->fetchOneBy('id', $id);
        if($this->getDao()->delete($wordNotGoodDto))
        {
            $this->_redirect($this->_request->getControllerName());
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
        $itemDto->updated_at = Qsoft_Helper_Datetime::currentTime();
        
        $id = $this->getDao()->insert($itemDto);
        return array('status' => true, 'primary' => $id);
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