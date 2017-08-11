<?php

class SingleWordController extends Qsoft_Controller_Backend_Action
{ 
    protected $_daoClass = 'Dao_SingleWord';     
    
    protected $_filterClass = 'Filter_SingleWord';    
	
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
            if($this->_request->getPost('description') == null) {
                $this->_redirect($this->_request->getControllerName());
            }    
            $this->getDao()->dontDisplayProfile($this->_request->getPost('description', array()));
            $this->noticeMessage("Your selected items has been hide already");
        }
        
        $page = $this->_getParam('page', 1);
        $query = $this->_request->getQuery();

        $config = Zend_Registry::get($this->_request->getModuleName() . '_config');
        $limit = $config->list->itemPerPage;
        
        if ($query != null) {
            $this->view->items = $this->getPagination($page, $limit, $query);    
        } else {
            $this->view->items = array();
        }        

        if ($this->_filterClass) {
            $this->view->filterForm = new $this->_filterClass;
            $this->view->filterForm->populate($query);
        }

        $this->view->query = $query;
        $this->view->title = $this->view->translate(ucfirst($this->_request->getControllerName()) . ' list');
    }
    
}