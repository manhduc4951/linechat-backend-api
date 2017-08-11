<?php

/**
 * Dao_Chat_Factory class
 * Use for factory Dao_Chat instances
 * 
 * @package LineChatApp
 * @subpackage  Dao_Chat
 * @author duyld
 */
class Dao_Chat_Factory
{
    
    /**
     * Factory new Dao chat instance
     * 
     * @param   string  $class      The class name
     * @param   array   $config     Configuration for table
     * @return  Qsoft_Db_Table_Abstract
     */
    public static function create($class, $config = array())
    {
        if (substr($class, 0, 9) !== 'Dao_Chat_') {
            $class = 'Dao_Chat_' . $class;
        }
        
        if ( ! class_exists($class)) {
            throw new Zend_Exception("Class " . $class . " does not exist.");
        }
        
        // use if the chat database is different wit main database adapter
        //$chatAdapter = Zend_Registry::get('chatAdapter');
        //$config[Zend_Db_Table_Abstract::ADAPTER] = $chatAdapter;
        
        return new $class($config);
    }
    
}