<?php

/**
 * Dao_Roulette
 * 
 * @package Dao
 * @author ducdm
 */
class Dao_Roulette extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable
{

    protected $_name = 'roulette';
    
    protected $_rowClass = 'Dto_Roulette';
    
    public function doFilter(array $criteria = array(), Zend_Db_Select $select = null)
	{	    
        
        
		if ( ! $select instanceof Zend_Db_Select) {
			$select = $this->select()
                    ->from($this->_name)
                    ->setIntegrityCheck(false)
                    ->joinLeft('item', 'roulette.item_id = item.item_name', array('item_type', 'item_name'))
                    ->joinLeft('gift', 'roulette.gift_id = gift.gift_id', array('gift_id','gift_category_id'))
                    ;
		}        
        //echo $select; die;        
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
