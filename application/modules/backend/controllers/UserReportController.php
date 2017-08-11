<?php

class UserReportController extends Qsoft_Controller_Backend_Action
{ 
    protected $_daoClass = 'Dao_UserReport';
    
    protected $_businessClass = 'Business_Message';
    
    protected $_filterClass = 'Filter_UserReport';    
    
	
	/**
     * Initialize method
     */
    public function init()
    {
        parent::init();
        
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/modules/backend/configs/navigation.ini', 'report_nav');
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
     * Get the business model
     * 
     * @return Business_User
     */
	public function getBusiness()
	{
		return parent::getBusiness();
	}
    
    /**
     * Send reply report to an user
     * 
     * @return an Dto_User
     */
    public function sendAction()
    {        
        
        $id = (int) $this->_request->getParam('id', 0);        
        $userReportDto = $this->getDao()->fetchOneBy('user_report_id ',$id);
        
        if ($id != 0 AND !$userReportDto) {
            $this->_redirect($this->_request->getControllerName());
        }
        
        $userDao = new Dao_User();
        $userDto = $userDao->fetchOne($userReportDto->report_user_id);
        
        if($this->_request->isPost())
        {  
            $result = $this->getBusiness()->sendMessage($userDto, $this->_request->getPost('reply_content'));
            
            $url = Qsoft_Helper_Url::generate($this->_request->getControllerName());
            $backLink = '<a href="' . $url . '">' . $this->view->translate('Back to list') . '</a>';
            
            if($result['status'] === true) {
                $userReportDto->reply = $this->_request->getPost('reply_content');
                $userReportDto->reply_flg = 1;
                $this->getDao()->update($userReportDto);
                $this->noticeMessage('Sent message successfully. %s', $backLink);
            } else {
                $this->noticeMessage('Cannot send message, check support user %s', $backLink);
            }
            
        }        
        
        $this->view->item = $userReportDto;    
    }
    
}