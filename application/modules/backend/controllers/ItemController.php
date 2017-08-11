<?php

class ItemController extends Qsoft_Controller_Backend_Action
{ 
	
    protected $_daoClass = 'Dao_Item';
    
    protected $_formClass = 'Form_Item';
	
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
     * @return Dao_Item
     */
	public function getDao()
	{
		return parent::getDao();
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
        $itemDto->public_date = date('Y-m-d h:i:s', strtotime($itemDto->public_date));
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
        if($itemDto->item_img == null)
        {
            $array_field_name = $this->getFormUpdateFields($form);
            unset($array_field_name[array_search('item_img',$array_field_name)]);
            unset($itemDto->item_img);            
        } else {
            @unlink(realpath(Zend_Registry::get('app_config')->item->image->uploadPath.$oldDto->item_img));     
        }
        
        $itemDto->public_date = date('Y-m-d H:i:s', strtotime($itemDto->public_date));
        $itemDto->updated_at = Qsoft_Helper_Datetime::currentTime();
        $array_field_name[] = 'updated_at';
        unset($array_field_name[array_search('back',$array_field_name)]);
        //unset($itemDto->item_name);
        //unset($array_field_name[array_search('item_name',$array_field_name)]);
               
        $this->getDao()->update($itemDto, $array_field_name);
        return array('status' => true);
    }
}