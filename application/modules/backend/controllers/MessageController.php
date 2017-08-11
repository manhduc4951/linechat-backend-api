<?php

class MessageController extends Qsoft_Controller_Backend_Action
{
    
	protected $_businessClass = 'Business_Message';
	
    protected $_daoClass = 'Dao_Message';
    
    protected $_filterClass = 'Filter_Message';
    
	/**
     * Initialize method
     */
    public function init()
    {
        parent::init();
        
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/modules/backend/configs/navigation.ini', 'user_nav');
        $this->view->secondNavigation = new Zend_Navigation($config);
    }
    
    /**
     * Get the business model
     * 
     * @return Business_User
     */
	public function getBusiness()
	{
		return parent::getBusiness();
	}
	
	/**
     * Get the Dao object
     * 
     * @return Dao_Message
     */
	public function getDao()
	{
		return parent::getDao();
	}
    
    public function getDaoUser()
    {
        return new Dao_User();
    }
    
    /**
     * Send message to an user
     * 
     * @return an Dto_User
     */
    public function sendAction()
    {
        $id = (int) $this->_request->getParam('id', 0);
        $userDto = $this->getDaoUser()->fetchOneWithAvatar('user.id',$id);
        
        if($this->_request->isPost())
        {            
            $result = $this->getBusiness()->sendMessage($userDto, $this->_request->getPost('text'));
            
            $url = Qsoft_Helper_Url::generate($this->_request->getControllerName());
            $backLink = '<a href="' . $url . '">' . $this->view->translate('Back to list') . '</a>';
            
            if($result['status'] === true) {
                $this->noticeMessage('Sent message successfully. %s', $backLink);
            } else {
                $this->noticeMessage('Cannot send message, check support user %s', $backLink);    
            }
        }        
        
        if ($id != 0 AND !$userDto) {
            $this->_redirect($this->_request->getControllerName());
        }
        
        $this->view->item = $userDto;    
    }
    
    /**
     * List users
     * 
     * @return some Dto_User
     */
    public function broadcastAction()
    {
        parent::indexAction();
        $this->view->action_name = $this->_request->getActionName();
        $this->render('index');        
    }

    /**
     * Send message to some users
     * 
     * @return some Dto_User
     */
    public function sendbroadcastAction()
    {
        $page = null;
        $query = $this->_request->getQuery();
        foreach ($this->getPagination($page, false, $query) as $key => $value)
        {
            $array_id[] = $value->id; 
        }        
        $usersDto = $this->getDaoUser()->fetchAllBy('id', $array_id);        
        
        if($this->_request->isPost())
        {
            $result = $this->getBusiness()->sendMessage($usersDto, $this->_request->getPost('text'));
            
            $url = Qsoft_Helper_Url::generate($this->_request->getControllerName().'/broadcast');
            $backLink = '<a href="' . $url . '">' . $this->view->translate('Back to list') . '</a>'; 
            
            if($result['status'] === true) {
                $this->noticeMessage('Sent message successfully. %s', $backLink);
            } else {
                $this->noticeMessage('Cannot send message, check support user %s', $backLink);      
            }          
        }        
        
        $this->view->items = $this->getPagination($page, false, $query);
        
        $this->view->query = $query;
        //$this->view->title = $this->view->translate(ucfirst($this->_request->getControllerName()) . ' list');    
    }
}