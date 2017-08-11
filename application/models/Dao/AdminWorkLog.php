<?php

/**
 * Dao_AdminWorkLog
 * 
 * @package Dao
 * @author ducdm
 */
class Dao_AdminWorkLog extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable
{
    
    protected $_name = 'admin_work_log';
    
    protected $_rowClass = 'Dto_AdminWorkLog';
    
    public function doFilter(array $criteria = array(), Zend_Db_Select $select = null)
    {
        if ( ! $select instanceof Zend_Db_Select) {
            $select = $this->select()
                ->from($this->_name)
                ->setIntegrityCheck(false)
                ->joinLeft('admin_user', 'admin_work_log.work_admin_user_id = admin_user.admin_user_id', array('login_id AS work_login_id'))
                ->joinLeft('admin_user AS admin_user2', 'admin_work_log.target_admin_user_id = admin_user2.admin_user_id', array('login_id AS target_login_id'))
                ->order('admin_work_log.created_at DESC')
                ; 
                              
        }
        
        // Search by admin user id
		if (isset($criteria['id'])) {
			$select->where('admin_work_log.work_admin_user_id = ?', $criteria['id']);
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