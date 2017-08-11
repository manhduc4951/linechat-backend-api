<?php

/**
 * Dao_AclRole
 * 
 * @package Dao
 * @author duyld
 */
class Dao_AclRole extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable
{
    
    protected $_name = 'acl_role';
    
    protected $_rowClass = 'Dto_AclRole';
    
    const ANONYMOUS_ROLE_ID = 0;
    const ADMINISTRATOR_ROLE_ID = 1;
    const MODERATOR_ROLE_ID = 2;
    const PARTTIME_ROLE_ID = 3;
    const ANONYMOUS_ROLE_NAME = '匿名の';
    
    public function getPagination($page = 1, $limit = null, $select = null, $fetchArray = false)
    {   
        if (is_array($select)) {
            $select = $this->doFilter($select);
        } elseif (!$select instanceof Zend_Db_Select) {
            $select = $this->select();
            $primary = $this->getPrimaryKey();
            $select->order("{$primary} asc");
        }
        
        return parent::getPagination($page, $limit, $select, $fetchArray);
    }
    
}