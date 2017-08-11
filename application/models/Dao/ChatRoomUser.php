<?php

/**
 * Dao_ChatRoomUser
 * 
 * @package Dao
 * @author duyld
 */
class Dao_ChatRoomUser extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable
{

    protected $_name = 'chat_room_user';
    
    protected $_rowClass = 'Dto_ChatRoomUser';
    
    /**
     * Generate filter query
     * 
     * @param   array               $options
     * @param   null|Zend_Db_Select $select
     * @return  Zend_Db_Select
     */
    public function doFilter(array $options = array(), Zend_Db_Select $select = null)
    {
        if ( ! $select instanceof Zend_Db_Select) {
            $select = $this->select();
        }
        
        if (isset($options['room_id']) AND strlen($options['room_id'])) {
            $select->where('room_id = ?', $options['room_id']);
        }
        
        if (isset($options['from']) AND strlen($options['from'])) {
            $select->where('created_at >= ?', Qsoft_Helper_Datetime::time($options['from']));
        }
        
        if (isset($options['to']) AND strlen($options['to'])) {
            $select->where('created_at <= ?', Qsoft_Helper_Datetime::time($options['to']));
        }
        
        if ( ! empty($options['state'])) {
            $select->where('state IN (?)', $options['state']);
        }
        
        return $select;
    }
    
}
