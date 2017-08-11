<?php

/**
 * Dao_Pregroup
 * 
 * @package Dao
 * @author ducdm
 */
class Dao_Pregroup extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable
{

    protected $_name = 'pre_group';
    
    protected $_rowClass = 'Dto_Pregroup';
    
    /**
     * Generate filter query to display in the user list
     * 
     * @param   array 				$options
	 * @param	null|Zend_Db_Select	$select
     * @return  Zend_Db_Select
     */
    public function doFilter(array $options = array(), Zend_Db_Select $select = null)
    {
        return $this->select()
                ->from($this->_name)
                ->setIntegrityCheck(false)
                ->joinLeft('region','pre_group.pref = region.id')
                ->joinLeft('user_age','pre_group.age = user_age.id');
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
