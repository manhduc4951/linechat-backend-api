<?php

class AdminWorkLogController extends Qsoft_Controller_Backend_Action
{ 
	
    protected $_daoClass = 'Dao_AdminWorkLog';
	
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
        $page = $this->_getParam('page', 1);
        $query = array();
        $id = $this->_request->getParam('id');
        if ($id) $query = array('id' => $id);

        $config = Zend_Registry::get($this->_request->getModuleName() . '_config');
        $limit = $config->list->itemPerPage;

        $this->view->items = $this->getPagination($page, $limit, $query);

        if ($this->_filterClass) {
            $this->view->filterForm = new $this->_filterClass;
            $this->view->filterForm->populate($query);
        }

        $this->view->query = $query;
        $this->view->title = $this->view->translate(ucfirst($this->_request->getControllerName()) . ' list');
    }
    
}