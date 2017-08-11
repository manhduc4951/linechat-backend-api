<?php

/**
 * Dao_AppStartLog
 * 
 * @package Dao
 * @author duyld
 */
class Dao_AppStartLog extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable
{

    protected $_name = 'app_start_log';
    
    protected $_rowClass = 'Dto_AppStartLog';
    
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
                           ->from(
                                $this->_name, array(
                                "created_at",                                
                                "COUNT(DISTINCT user_id) AS unique_count",                
                                "COUNT(*) AS total",
                                )
                            )            
                            ->group("DATE(created_at)")
                            ->group("HOUR(created_at)")            
                            ->order("DATE(created_at) desc")
                            ;
		}
        
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