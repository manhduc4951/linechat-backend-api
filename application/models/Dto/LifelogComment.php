<?php

/**
 * Dto_LifelogComment
 * 
 * @package Dto
 * @author ducdm
 */
class Dto_LifelogComment extends Qsoft_Dto_Abstract
{
    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_LifelogComment';
    
    /**
     * Return the array that contains the end user data
     * 
     * @return array
     */
    public function toEndUserArray()
    {
        $extracted = array('id', 'user_id', 'lifelog_id', 'comment', 'created_at');
        $array = $this->toArray($extracted);
        return $array;
    }
}