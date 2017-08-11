<?php

class UserAvatarController extends Qsoft_Controller_Backend_Action
{ 
	
    protected $_daoClass = 'Dao_UserAvatar';
    
    protected $_formClass = 'Form_UserAvatar';
	
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
     * @return Dao_UserAvatar
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
     * Update an existing record from modify action
     * 
     * @param mixed $itemDto
     * @param mixed $form
     * @param mixed $oldDto
     * @return array
     */
    protected function doUpdate($itemDto, $form, $oldDto)
    {         
        if($itemDto->avatar_img == null)
        {
            $array_field_name = $this->getFormUpdateFields($form);
            unset($array_field_name[array_search('avatar_img',$array_field_name)]);
            unset($itemDto->avatar_img);
            
        } else {
            @unlink(realpath(Zend_Registry::get('app_config')->user->avatar->uploadPath.$oldDto->avatar_img));     
        }
        $itemDto->updated_at = Qsoft_Helper_Datetime::currentTime();
        $array_field_name[] = 'updated_at';
        unset($array_field_name[array_search('back',$array_field_name)]);        
               
        $this->getDao()->update($itemDto, $array_field_name);
        return array('status' => true);
    }
}