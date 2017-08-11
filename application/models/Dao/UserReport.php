<?php

/**
 * Dao_UserReport
 * 
 * @package Dao
 * @author ducdm
 */
class Dao_UserReport extends Qsoft_Db_Table_Abstract
    implements Dao_Interface_EntityAccessable
{

    protected $_name = 'user_report';
    
    protected $_rowClass = 'Dto_UserReport';
    
    /**
     * Generate filter query to display in the user list
     * 
     * @param   array 				$options
	 * @param	null|Zend_Db_Select	$select
     * @return  Zend_Db_Select
     */
	public function doFilter(array $criteria = array(), Zend_Db_Select $select = null)
	{
		if ( ! $select instanceof Zend_Db_Select) {
			$select = $this->select()
                      ->from($this->_name)
                      ->setIntegrityCheck(false)
                      ->joinLeft('user AS user1','user_report.report_user_id = user1.id', array('user1.nick_name AS user_report_nick_name'))
                      ->joinLeft('user AS user2','user_report.target_user_id = user2.id', array('user2.nick_name AS user_target_nick_name'))
                      ;                     
                      
		}
        
        if (isset($criteria['nick_name']) AND strlen($criteria['nick_name'])) {
            $select->where("user1.nick_name LIKE ?", "%{$criteria['nick_name']}%")
                   ->orWhere("user2.nick_name LIKE ?", "%{$criteria['nick_name']}%");
        }
        
        if (isset($criteria['created_at_from']) AND strlen($criteria['created_at_from'])) {
            $select->where('DATE_FORMAT(user_report.created_at, \'%Y/%m/%d\') >= ?', $criteria['created_at_from']);
        }
        if (isset($criteria['created_at_to']) AND strlen($criteria['created_at_to'])) {
            $select->where('DATE_FORMAT(user_report.created_at, \'%Y/%m/%d\') <= ?', $criteria['created_at_to']);
        }
                
		return $select;
        
	}
    

    /**
     * Return list pagination
     * 
     * @param   int             $page
     * @param   int             $limit
     * @param   array|Zend_Db_Select  $select
     * @param   boolean         $fetchArray     Fetch the results item as array
     * @return  Zend_Paginator
     */
    public function getPagination($page = 1, $limit = null, $select = null, $fetchArray = false)
    {   
        if (is_array($select)) {
            $select = $this->doFilter($select);
        } elseif (!$select instanceof Zend_Db_Select) {
            $select = $this->select();
        }
        
        return parent::getPagination($page, $limit, $select, $fetchArray);
    }    
    
    /**
     * Retrieve a instance by provided name and value
     * 
     * @param   string|array  $name
     * @param   string|array  $value
     * @param   Zend_Db_Select  $select
     * @return  Qsoft_Dto_Abstract
     */
    public function fetchOneBy($name, $value = '', Zend_Db_Select $select = null)
    {
        if ( ! $select instanceof Zend_Db_Select) {
            $select = $this->select()
                      ->from($this->_name)
                      ->setIntegrityCheck(false)
                      ->joinLeft('user AS user1','user_report.report_user_id = user1.id', array('user1.nick_name AS user_report_nick_name'))
                      ->joinLeft('user AS user2','user_report.target_user_id = user2.id', array('user2.nick_name AS user_target_nick_name'))
                      ;
        }
        
        if ( ! is_array($name)) {
            $name = array($name => $value);
        }
        
        foreach ($name as $field => $value) {
            $select->where($field . ' = ?', $value);
        }
        
        return $this->fetchRow($select);
    }   
}
