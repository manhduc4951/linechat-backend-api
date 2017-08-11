<?php

class AclRoleController extends Qsoft_Controller_Backend_Action
{ 
	
    protected $_daoClass = 'Dao_AclRole';
    
    protected $_formClass = 'Form_AclRole';
	
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
    
    public function indexAction()
    {
        $id = $this->_request->getPost('role_id');
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
                    $this->flashNoticeMessage('Item has been saved successfully.');
                    
                    if ( ! $id) {
                        $id = $itemDto->{$this->getDao()->getPrimaryKey()};
                    }
                    
                    $this->_redirect($this->_request->getControllerName() . '/index');
                } else {
                    $this->warningMessage('error_' . $result['error_code']);
                }
            }
        }
        $this->view->form = $form;
        parent::indexAction();
    }
    
    public function deleteAction()
    {
        $this->getHelper('viewRenderer')->setNoRender();
        $id = $this->_request->getParam('id', 0);
        $item = $this->getDao()->fetchOne($id);
        
        if ($id != 0 AND !$item) {
            $this->_redirect($this->_request->getControllerName());
        }
        // check role higher or lower than user's role
        if(Zend_Auth::getInstance()->getIdentity()->admin_role_id >= $id) {
            $this->flashWarningMessage('You cannot delete a role which is higher than or equal your role');
            $this->_redirect($this->_request->getControllerName());     
        }
        // check role is still used
        $adminUserDao = new Dao_AdminUser();
        $adminUserRole = $adminUserDao->fetchAllBy('admin_role_id', $id)->count();
        if($adminUserRole > 0) {
            $this->flashWarningMessage('Role is still used, cannot delete');
            $this->_redirect($this->_request->getControllerName());    
        }
        // delete role
        if($this->getDao()->delete($item))
        {
            $this->flashNoticeMessage('Item has been deleted successfully.');
            $this->_redirect($this->_request->getControllerName());
        }
    } 
    
}