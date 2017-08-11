<?php

/**
 * Dto_Chat_PubsubSupscription
 * 
 * @package Dto_Chat
 * @author duyld
 */
class Dto_Chat_PubsubSupscription extends Qsoft_Dto_Abstract
{
    
    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_Chat_PubsubSupscription';
    
    
    /**
     * Convert to array that contains public data
     * 
     * @return  array
     */
    public function toEndUserArray()
    {
        $jid = new XMPPJid($this->jid);
        $array['id'] = $jid->node;
        
        $array['state'] = $this->state;
        
        return $array;
    }
    
    /**
     * Convert to string
     *
     * @param string $value    The column name
     * @param string $default
     * @return string
     */
    public function toString($value, $default = null)
    {
        if (isset($this->{$value})) {
            if ($value == 'jid') {
                $jid = new XMPPJid($this->jid);
                return $jid->node;
            }
            
            return $this->{$value};
        }
         
        return $default;
    }
    
}
