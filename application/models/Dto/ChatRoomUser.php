<?php

/**
 * Dto_ChatRoomUser
 * 
 * @package Dto
 * @author duyld
 */
class Dto_ChatRoomUser extends Qsoft_Dto_Abstract
{
    
    const STATE_JOIN 	= 'join';
    const STATE_LEFT 	= 'left';
    const STATE_INVITED	= 'invited';
    
    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_ChatRoomUser';
    
    /**
     * __toString()
     * 
     * @return string
     */
    public function __toString()
    {
    	return $this->room_id;
    }
    
    /**
     * Convert to array that contains only public data
     * 
     * @return  array
     */
    public function toEndUserArray()
    {
        return $this->toArray(array('unique_id', 'nick_name', 'state', 'created_at'));
    }
    
}