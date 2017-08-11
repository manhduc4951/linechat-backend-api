<?php

class Business_Abstract
{
    
    /**
     * User DAO
     * 
     * @var Dao_User
     */
    protected $userDao;
    
    /**
     * Constructor
     * 
     * @return Business_User
     */
    public function __construct()
    {
        $this->userDao = new Dao_User();
        
        return $this;
    }
    
    /**
     * Read the response object from xmpp library and convert to
     * business result array
     * 
     * @param   XmppResponse    $response
     * @return  array           Result array
     */
    protected function _readXmppResponse(XmppResponse $response)
    {
        if ($response->isFailure()) {
            switch ($response->code) {
                case Xmpp::ERROR_CONNECTION:
                    return array('status' => false, 'error_code' => ERROR_CONNECTION_CHAT_SERVER);
                case Xmpp::ERROR_AUTH_FAILURE:
                    return array('status' => false, 'error_code' => ERROR_AUTH_FAILURE_CHAT_SERVER);
                default:
                    return array('status' => false, 'error_code' => ERROR_UNKNOWN_FROM_CHAT_SERVER);
            }
        }
        
        return array('status' => true);
        
    }
    
    /**
     * Convert mixed value to xmpp bare jid
     * 
     * @param   Dto_User|array|string     $value    User dto instance(s) or just the unique id
     * @return  void
     */
    public function toBareJid($value)
    {
        if (is_array($value) OR $value instanceof Zend_Db_Table_Rowset_Abstract) {
            $users = array();
            foreach ($value as $userDto) {
                $users[] = $this->toBareJid($userDto);
            }
            
            return $users;
        }
        
        if ( ! $value instanceof Dto_User) {
            $value = $this->userDao->fetchOnePublicBy('unique_id', $value);
        }
        
        if ( ! $value) {
            return null;
        }
        
        $bareJid = XmppFactory::createBareJid($value->getChatUsername());
        return $bareJid;
    }
    
    /**
     * Get support user instance
     * 
     * @return  Dto_User
     * @throws LogicException
     */
    public function getSupportUser()
    {
        $userDao = new Dao_User();
        
        $config = Zend_Registry::get('backend_config');
        $support_user = $config->support_user->id;
        
        if ( ! $supportUserDto = $this->userDao->fetchOne($support_user)) {
       		throw new LogicException('Cannot found the support account');
        }

        return $supportUserDto;    
    }
    
}