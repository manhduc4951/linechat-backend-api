<?php

/**
 * XmppFactory class
 * 
 * @package LCA450
 * @subpackage App_Helper
 * @author duyld
 */
class XmppFactory
{
    
    /**
     * Create new Xmpp instance
     * 
     * @param   Dto_User|string     $jid        May be user DTO instance of bare jid
     * @param   string              $password
     * @return  Xmpp
     */
    public static function create($jid = null, $password = null)
    {
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/xmpp.ini');
        $config = $config->xmpp;
        
        if ($jid instanceof Dto_User) {
            $password = (null === $password) ? $jid->getChatPassword() : $password;
            $jid = self::createBareJid($jid->getChatUsername());
        }
        
        if (null === $jid) {
        	$jid = $config->domain;
        }
        
        return new Xmpp($config, $jid, $password);
    }
    
    /**
     * Create an bare jid
     * 
     * @param   string $id      The chat id
     * @return  string
     */
    public static function createBareJid($id)
    {
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/xmpp.ini');
        return $id . '@' . $config->xmpp->domain;
    }
    
}