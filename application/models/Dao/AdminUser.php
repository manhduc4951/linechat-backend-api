<?php

/**
 * Dao_AdminUser
 * 
 * @package Dao
 * @author duyld
 */
class Dao_AdminUser extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable
{
    
    protected $_name = 'admin_user';
    
    protected $_rowClass = 'Dto_AdminUser';
    
    public function doFilter(array $criteria = array(), Zend_Db_Select $select = null)
    {
        if ( ! $select instanceof Zend_Db_Select) {
            $select = $this->select()
                ->from($this->_name)
                ->setIntegrityCheck(false)
                ->joinLeft('acl_role', 'admin_user.admin_role_id = acl_role.role_id', array('role_name'))
                ; 
                              
        }
        
        return $select;
                
    }
    
    public function getPagination($page = 1, $limit = null, $select = null, $fetchArray = false)
    {   
        if (is_array($select)) {
            $select = $this->doFilter($select);
        } elseif (!$select instanceof Zend_Db_Select) {
            $select = $this->select();
        }
        
        return parent::getPagination($page, $limit, $select, $fetchArray);
    }
    
}