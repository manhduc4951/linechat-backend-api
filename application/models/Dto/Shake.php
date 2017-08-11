<?php

/**
 * Dto_Shake
 * 
 * @package Dto
 * @author duyld
 */
class Dto_Shake extends Qsoft_Dto_Abstract
{
    
    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_Shake';
    
    /**
     * Return the array that contains the end user data
     * 
     * @return array
     */
    public function toEndUserArray()
    {
        $extracted = array('user_id');
        return $this->toArray($extracted);
    }
    
}