<?php

/**
 * Dao_Gift
 * 
 * @package Dao
 * @author ducdm
 */
class Dao_Gift extends Qsoft_Db_Table_Abstract
    implements Dao_Interface_EntityAccessable
{
    
    protected $_name = 'gift';
    
    protected $_rowClass = 'Dto_Gift';
    
    public function doFilter(array $criteria = array(), Zend_Db_Select $select = null)
	{
		if ( ! $select instanceof Zend_Db_Select) {
			$select = $this->select();                                          
                      
		}
		
        // Search user by id
		if (isset($criteria['gift_category_id']) and strlen($criteria['gift_category_id'])) {
			$select->where('gift_category_id = ?', $criteria['gift_category_id']);
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
