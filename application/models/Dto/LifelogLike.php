<?php

/**
 * Dto_LifelogLike
 * 
 * @package Dto
 * @author ducdm
 */
class Dto_LifelogLike extends Qsoft_Dto_Abstract
{
    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_LifelogLike';
    
    /**
     * Return the array that contains the end user data
     * 
     * @return array
     */
    public function toEndUserArray()
    {
        $extracted = array('id', 'lifelog_id', 'sticker', 'created_at');
        $array = $this->toArray($extracted);
        return $array;
    }
}