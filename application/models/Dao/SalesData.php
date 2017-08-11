<?php

/**
 * Dao_SalesData
 * 
 * @package Dao
 * @author  ducdm
 */
class Dao_SalesData extends Qsoft_Db_Table_Abstract
    implements Dao_Interface_EntityAccessable
{
    
    protected $_name = 'sales_data';
    
    protected $_rowClass = 'Dto_SalesData';
    
    public function doFilter(array $criteria = array(), Zend_Db_Select $select = null)
	{	    
        $field_select = array('created_at',
                       'SUM(point) AS sum_point',
                       'SUM(amount) AS sum_amount',
                      );
        
		if ( ! $select instanceof Zend_Db_Select) {
			$select = $this->select()
                           ->from($this->_name, $field_select)
                           ->group("DATE(created_at)")
                           ->group("HOUR(created_at)")            
                           ->order("DATE(created_at) desc")
                           ;                                         
                      
		}
        
        // Search user by created at
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
    
}
