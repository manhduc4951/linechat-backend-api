<?php

/**
 * Dao_UserUseLog
 * 
 * @package Dao
 * @author ducdm
 */
class Dao_UserUseLog extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable
{

    protected $_name = 'user_use_log';
    
    protected $_rowClass = 'Dto_UserUseLog';    
    
    /**
     * Generate filter query to display in the user list
     * 
     * @param   array 				$criteria
	 * @param	null|Zend_Db_Select	$select
     * @return  Zend_Db_Select
     */
    public function doFilter(array $criteria = array(), Zend_Db_Select $select = null)
    {          
        if ( ! $select instanceof Zend_Db_Select) {
			$select = $this->select()
                           ->from($this->_name)
                           ->setIntegrityCheck(false)
                           ->joinLeft('user', $this->_name.'.user_id = user.id', array('nick_name', 'user.user_id AS user_id_text'))                           
                           ->order('created_at DESC')
                           ;
		
        }
        
        // search by created_at
        if (isset($criteria['created_at_from']) AND strlen($criteria['created_at_from'])) {
            $select->where('DATE_FORMAT('.$this->_name.'.created_at, \'%Y/%m/%d\') >= ?', $criteria['created_at_from']);
        }
        if (isset($criteria['created_at_to']) AND strlen($criteria['created_at_to'])) {
            $select->where('DATE_FORMAT('.$this->_name.'.created_at, \'%Y/%m/%d\') <= ?', $criteria['created_at_to']);
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
