<?php

/**
 * Dao_SummaryAdvertisement
 * 
 * @package Dao
 * @author  ducdm
 */
class Dao_SummaryAdvertisement extends Qsoft_Db_Table_Abstract
    implements Dao_Interface_EntityAccessable
{
    
    protected $_name = 'total_advertisement_data';
    
    protected $_rowClass = 'Dto_SummaryAdvertisement';
    
    public function doFilter(array $criteria = array(), Zend_Db_Select $select = null)
	{	    
        $field_select = array('date_hour',
                       'SUM(download_count) AS sum_download_count',
                       'SUM(regist_count) AS sum_regist_count',
                       "sum(if(media_group=1,1,0)) as media_group_page",
                       "sum(if(media_group=2,1,0)) as media_group_web",
                       "sum(if(media_group=3,1,0)) as media_group_ad",                        
                      );
        
		if ( ! $select instanceof Zend_Db_Select) {
			$select = $this->select()
                           ->from($this->_name, $field_select)
                           ->group("DATE(date_hour)")
                           ->group("HOUR(date_hour)")            
                           ->order("DATE(date_hour) desc")
                           ;                                         
                      
		}
        
        // Search user by code ad
		if (isset($criteria['advertisement_code']) AND strlen($criteria['advertisement_code'])) {
            $select->where("advertisement_code LIKE ?", "%{$criteria['advertisement_code']}%");
        }
        
        // Search user by media group
		if (isset($criteria['media_group']) and strlen($criteria['media_group'])) {
			$select->where('media_group = ?', $criteria['media_group']);
		}
        
        // Search user by date hour
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
