<?php

/**
 * Business_Message class
 *
 * @package LineChatApp
 * @subpackage Business 
 */
class Business_Message extends Business_Abstract
{
    /**
     * Send message to an user or some users
     * 
     * @param Dto_User|Zend_Db_Table_Rowset_Abstract|array $id
     * @param string $message   Content of message 
     * @return  array
     */
    public function sendMessage($id, $message)
    {      
        if ( ! is_array($id) AND ! $id instanceof Zend_Db_Table_Rowset_Abstract) {
            $id = array($id);
        }
        
        $id = $this->toBareJid($id);
        
        $client = XmppFactory::create($this->getSupportUser());
        
        $response = $client->sendMessage($message, $id);
        if($response) {
            return $this->_readXmppResponse($response);    
        } else {
            return array('status' => 'false');
        }     
            
    }
}    