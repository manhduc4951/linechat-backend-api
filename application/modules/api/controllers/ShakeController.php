<?php

class Api_ShakeController extends App_Rest_Controller
{
    
    protected $_businessClass = 'Business_Shake';
    
    protected $_daoClass = 'Dao_UserShake';
    
    /**
     * Get the dbtable
     * 
     * @return Dao_UserShake
     */
    protected function getDao()
    {
        return parent::getDao();
    }
    
    /**
     * Get the business model
     * 
     * @return Business_Shake
     */
    protected function getBusiness()
    {
        return parent::getBusiness();
    }
    
    /**
     * Ping service to start a shake
     */
    public function postAction()
    {
        $userDto = Zend_Registry::get('api_user');
        $longitude = $this->_getParam('longitude', null);
        $latitude = $this->_getParam('latitude', null);
        if ( ! strlen($longitude) OR ! strlen($latitude)) {
            $this->badRequestAction();
        }
        
        $result = $this->getBusiness()->startLookUp($userDto, $longitude, $latitude);
        
        $this->fromArray($result);
    }
    
    /**
     * Do lookup
     */
    public function indexAction()
    {
    	$userDto = Zend_Registry::get('api_user');
    	$result = $this->getBusiness()->lookUp($userDto);
    	
    	if (true !== $result['status']) {
    		$this->fromArray($result);
    	}
    	
        $this->_userDao->addImageData($result['users']);
    	$this->success(array('users' => $result['users']->toContactWithFriendStatusArray()));
    }
    
}