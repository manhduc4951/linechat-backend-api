<?php

class AdminUserController extends Qsoft_Controller_Backend_Action
{ 
	
    protected $_daoClass = 'Dao_AdminUser';
    
    protected $_formClass = 'Form_AdminUser';
	
	/**
     * Initialize method
     */
    public function init()
    {
        parent::init();
        
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/modules/backend/configs/navigation.ini', 'admin_nav');
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
    
    public function registerAction()
    {  
        $this->processForm();
        $this->view->form->setAttrib('class', 'index-form');
        $this->view->form->login_id->setDecorators(array('ViewHelper', 'Errors'));
        $this->view->form->admin_user_name->setDecorators(array('ViewHelper', 'Errors')); 
        $this->view->form->password->setDecorators(array('ViewHelper', 'Errors'));
        $this->view->form->submit->setDecorators(array('ViewHelper', 'Errors'));
           
    }
    
    public function deleteAction()
    {
        $this->getHelper('viewRenderer')->setNoRender();
        $id = $this->_request->getParam('id', 0);
        $item = $this->getDao()->fetchOne($id);
        
        if ($id != 0 AND !$item) {
            $this->_redirect($this->_request->getControllerName());
        }
        //prevent delete action when user don't have enough role or user try to delete themselves
        if (Zend_Auth::getInstance()->getIdentity()->admin_user_id == $item->admin_user_id
            or Zend_Auth::getInstance()->getIdentity()->admin_role_id >= $item->admin_role_id
           )
        {
            $this->flashWarningMessage('You cannot delete this user because you dont have enough role or you are trying to delete your user');
            $this->_redirect($this->_request->getControllerName()); 
        }
        // delete admin user
        $item->delete_flg = 1;
        if($this->getDao()->update($item))
        {
            // log to admin_work_log: delete
            $adminWorkLogDto = new Dto_AdminWorkLog();
            $adminWorkLogDto->work_admin_user_id = Zend_Auth::getInstance()->getIdentity()->admin_user_id;
            $adminWorkLogDto->ip_address = $this->getRequest()->getClientIp();
            $adminWorkLogDto->login_pc_name = gethostname();
            $adminWorkLogDto->target_admin_user_id = $id;
            $adminWorkLogDto->content = 'Delete'; 
            
            $adminWorkLogDao = new Dao_AdminWorkLog();
            $adminWorkLogDao->insert($adminWorkLogDto);
             
            $this->flashNoticeMessage('Item has been deleted successfully.');
            $this->_redirect($this->_request->getControllerName());
        }
    }
    
    protected function processForm()
    {
        $id = $this->_request->getParam('id', 0);
        $item = $this->getDao()->fetchOne($id);
        
        
        if ($id != 0 AND !$item) {
            $this->_redirect($this->_request->getControllerName());
        }
        $form = $this->createForm($item);
        
        if ($this->_request->isPost()) {
            
            
            if ($form->isValid($this->_request->getPost())) {
                $itemDto = $form->mapFormToDto($item);                               
                //prevent edit action when user grant role to higher than their role
                if ($itemDto->admin_role_id AND Zend_Auth::getInstance()->getIdentity()->admin_role_id > $itemDto->admin_role_id) {
                    $this->flashWarningMessage('You cannot grant role which higher than your role');
                    $this->_redirect($this->_request->getControllerName() . '/modify/id/' . $id);     
                }
                // prevent edit action when edit a user but dont have enough role
                if ($item) {
                    if (Zend_Auth::getInstance()->getIdentity()->admin_role_id >= $item->admin_role_id AND Zend_Auth::getInstance()->getIdentity()->admin_user_id != $id) {
                        $this->flashWarningMessage('You cannot edit this user');
                        $this->_redirect($this->_request->getControllerName() . '/modify/id/' . $id);    
                    }    
                }
                                
                if ($id) {
                    $result = $this->doUpdate($itemDto, $form, $item);
                    // log to admin_work_log: edit
                    $adminWorkLogDto = new Dto_AdminWorkLog();
                    $adminWorkLogDto->work_admin_user_id = Zend_Auth::getInstance()->getIdentity()->admin_user_id;
                    $adminWorkLogDto->ip_address = $this->getRequest()->getClientIp();
                    $adminWorkLogDto->login_pc_name = gethostname();
                    $adminWorkLogDto->target_admin_user_id = $id;
                    $adminWorkLogDto->content = 'Edit'; 
                    
                    $adminWorkLogDao = new Dao_AdminWorkLog();
                    $adminWorkLogDao->insert($adminWorkLogDto);
                } else {
                    $result = $this->doInsert($itemDto, $form, $item);
                    // log to admin_work_log: register
                    $adminWorkLogDto = new Dto_AdminWorkLog();
                    $adminWorkLogDto->work_admin_user_id = Zend_Auth::getInstance()->getIdentity()->admin_user_id;
                    $adminWorkLogDto->ip_address = $this->getRequest()->getClientIp();
                    $adminWorkLogDto->login_pc_name = gethostname();
                    $adminWorkLogDto->target_admin_user_id = $result['primary'];
                    $adminWorkLogDto->content = 'Register'; 
                    
                    $adminWorkLogDao = new Dao_AdminWorkLog();
                    $adminWorkLogDao->insert($adminWorkLogDto);  
                
                }

                if ($result['status'] === true) {

                    $indexUrl = Qsoft_Helper_Url::generate($this->_request->getControllerName());                    
                    $this->flashNoticeMessage('Item has been saved successfully.');
                    
                    if ( ! $id) {
                        $id = $itemDto->{$this->getDao()->getPrimaryKey()};
                    }
                    
                    $this->_redirect($this->_request->getControllerName() . '/modify/id/' . $id);
                } else {
                    $this->warningMessage('error_' . $result['error_code']);
                }
            }
        }
        // prevent view details a user which have higher role
        if (isset($item->admin_role_id) AND Zend_Auth::getInstance()->getIdentity()->admin_role_id > $item->admin_role_id) {
                    $this->flashWarningMessage('You cannot view detail this user');
                    $this->_redirect($this->_request->getControllerName());     
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
        $this->getDao()->update($itemDto);
        return array('status' => true);        
    }
    
    
}