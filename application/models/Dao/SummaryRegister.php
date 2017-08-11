<?php

/**
 * Dao_SummaryRegister
 * 
 * @package Dao
 * @author  ducdm
 */
class Dao_SummaryRegister extends Qsoft_Db_Table_Abstract
    implements Dao_Interface_EntityAccessable
{
    
    protected $_name = 'total_regist_data';
    
    protected $_rowClass = 'Dto_SummaryRegister';
    
    public function doFilter(array $criteria = array(), Zend_Db_Select $select = null)
	{	    
        $field_select = array('date_hour',
                       'SUM(regist_count) AS total',                       
                      );
                      
        $userAvatarDao = new Dao_UserAvatar();
        for($i = 1; $i <= $userAvatarDao->countUserAvatar(); $i++)
        {
            $field_select[] = "sum(if(avatar_id=$i,regist_count,0)) as avatar_$i";
        }
        
		if ( ! $select instanceof Zend_Db_Select) {
			$select = $this->select()
                           ->from($this->_name, $field_select)
                           ->group("DATE(date_hour)")
                           ->group("HOUR(date_hour)")            
                           ->order("DATE(date_hour) desc")
                           ;                                         
                      
		}
        
        if (isset($criteria['date_hour_from']) AND strlen($criteria['date_hour_from'])) {
            $select->where('DATE_FORMAT(date_hour, \'%Y/%m/%d\') >= ?', $criteria['date_hour_from']);
        }
        if (isset($criteria['date_hour_to']) AND strlen($criteria['date_hour_to'])) {
            $select->where('DATE_FORMAT(date_hour, \'%Y/%m/%d\') <= ?', $criteria['date_hour_to']);
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
