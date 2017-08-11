<?php

/**
 * Dao_SingleWord
 * 
 * @package Dao
 * @author ducdm
 */
class Dao_SingleWord extends Qsoft_Db_Table_Abstract
    implements Dao_Interface_EntityAccessable
{
    
    protected $_name = 'user';
    
    protected $_rowClass = 'Dto_SingleWord';
    
    public function doFilter(array $criteria = array(), Zend_Db_Select $select = null)
	{	    
        
        
		if ( ! $select instanceof Zend_Db_Select) {
			$select = $this->select();
		}
        
        // Search user by description
		if (isset($criteria['description']) AND strlen($criteria['description'])) {
            $select->where("description LIKE ?", "%{$criteria['description']}%");
        }
        
        // Search user by created_at
        if (isset($criteria['created_at_from']) AND strlen($criteria['created_at_from'])) {
            $select->where('DATE_FORMAT(created_at, \'%Y/%m/%d\') >= ?', $criteria['created_at_from']);
        }
        if (isset($criteria['created_at_to']) AND strlen($criteria['created_at_to'])) {
            $select->where('DATE_FORMAT(created_at, \'%Y/%m/%d\') <= ?', $criteria['created_at_to']);
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
    
    /**
     * Mark all profile hide
     * 
     * @param mixed $idArray
     * @return void
     */
    public function dontDisplayProfile($idArray = array())
    {
        if (!is_array($idArray)) {
            $idArray = array($idArray);
        }

        if ($idArray) {
            $where = $this->getAdapter()->quoteInto('id IN (?)', $idArray);
            $this->getAdapter()->update($this->_name, array('description_display' => '0'), $where);
        }
    }
    
}
