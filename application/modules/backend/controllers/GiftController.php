<?php

class GiftController extends Qsoft_Controller_Backend_Action
{ 
	
    protected $_daoClass = 'Dao_Gift';
    
    protected $_formClass = 'Form_Gift';
	
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
     * Listing all record
     */
    public function indexAction()
    {
        if ( ! $this->getRequest()->getParam( 'gift_category_id'))
        {
            $this->_redirect('giftcategory/index');
        }
        
        $gift_category_id = (int) $this->getRequest()->getParam( 'gift_category_id');
        $giftCategoryDao = new Dao_GiftCategory();
        $giftCategory = $this->getDao()->fetchOne($gift_category_id);

        if (!$giftCategory) {
            $this->_redirect('giftcategory/index');
        }
           
        $this->processForm();
        $this->view->form->setAttrib('class', 'index-form');
        $this->view->gift_category_id =  $gift_category_id;       
        parent::indexAction();        
        
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
        $itemDto->gift_category_id = $this->getRequest()->getParam( 'gift_category_id', null );
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
        if($itemDto->gift_img == null)
        {
            $array_field_name = $this->getFormUpdateFields($form);            
            unset($array_field_name[array_search('gift_img',$array_field_name)]);
            unset($itemDto->gift_img);  
                      
        } else {
            @unlink(realpath(Zend_Registry::get('app_config')->gift->image->uploadPath.$oldDto->gift_img));     
        }
        
        
        
        $itemDto->public_date = date('Y-m-d H:i:s', strtotime($itemDto->public_date));
        $itemDto->updated_at = Qsoft_Helper_Datetime::currentTime();
        $array_field_name[] = 'updated_at';
        unset($array_field_name[array_search('back',$array_field_name)]);
               
        $this->getDao()->update($itemDto, $array_field_name);
        return array('status' => true);
    }
    
    protected function processForm()
    {   
        $gift_category_id = (int)$this->_request->getParam('gift_category_id');
        $id = (int) $this->_request->getParam('id', 0);
        $item = $this->getDao()->fetchOne($id);

        if ($id != 0 AND !$item) {
            $this->_redirect($this->_request->getControllerName());
        }

        $form = $this->createForm($item);
        
        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getPost())) {
                $itemDto = $form->mapFormToDto($item);
                if ($id) {
                    $result = $this->doUpdate($itemDto, $form, $item);
                } else {
                    $result = $this->doInsert($itemDto, $form, $item);
                }

                if ($result['status'] === true) {

                    $indexUrl = Qsoft_Helper_Url::generate($this->_request->getControllerName());
                    //$backLink = '<a href="' . $indexUrl . '">' . $this->view->translate('Back to list') . '</a>';
                    $this->flashNoticeMessage('Item has been saved successfully.');
                    
                    if ( ! $id) {
                        $id = $result['primary'];
                    }
                    
                    $this->_redirect($this->_request->getControllerName() . '/modify/id/' . $id . '?gift_category_id='.$gift_category_id);
                } else {
                    $this->warningMessage('error_' . $result['error_code']);
                }
            }
        }
        
        $this->view->form = $form;
    }
}