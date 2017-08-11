<?php

/**
 * Dao_Message
 * 
 * @package Dao
 * @author ducdm
 */
class Dao_Message extends Qsoft_Db_Table_Abstract
    implements Dao_Interface_EntityAccessable
{

    protected $_name = 'user';
    
    protected $_rowClass = 'Dto_Message';
	
	
	/**
     * Generate filter query to display in the user list
     * 
     * @param   array 				$options
	 * @param	null|Zend_Db_Select	$select
     * @return  Zend_Db_Select
     */
	public function doFilter(array $criteria = array(), Zend_Db_Select $select = null)
	{
	    $field_select = array(
                       "sum(if(point_log.point > point_after,point_log.point-point_after,0)) as sum_purchase",
                       );
		if ( ! $select instanceof Zend_Db_Select) {
			$select = $this->select()
                      ->from($this->_name)
                      ->setIntegrityCheck(false)
                      ->joinLeft('avatar', 'user.avatar_id = avatar.avatar_id', array('avatar_img','sex','show_hide'))
                      ->joinLeft('point_log', 'user.id = point_log.user_id', $field_select)
                      ->group('user.id')
                      ;                     
                      
		}     
        
        // search by sex
        if (!empty($criteria['sex'])) {
            $select->where("avatar.sex IN (?)", $criteria['sex']);
        }
        
        // search by registration date
        if (isset($criteria['created_at_from']) AND strlen($criteria['created_at_from'])) {
            $select->where('DATE_FORMAT(user.created_at, \'%Y/%m/%d\') >= ?', $criteria['created_at_from']);
        }
        if (isset($criteria['created_at_to']) AND strlen($criteria['created_at_to'])) {
            $select->where('DATE_FORMAT(user.created_at, \'%Y/%m/%d\') <= ?', $criteria['created_at_to']);
        }
        
        // search by last access date
        if (isset($criteria['last_access_from']) AND strlen($criteria['last_access_from'])) {
            $select->where('DATE_FORMAT(user.last_access, \'%Y/%m/%d\') >= ?', $criteria['last_access_from']);
        }
        if (isset($criteria['last_access_to']) AND strlen($criteria['last_access_to'])) {
            $select->where('DATE_FORMAT(user.last_access, \'%Y/%m/%d\') <= ?', $criteria['last_access_to']);
        }
        
        // search by point
        if (isset($criteria['point_from']) AND strlen($criteria['point_from'])) {
            $select->where('user.point >= ?', $criteria['point_from']);
        }
        if (isset($criteria['point_to']) AND strlen($criteria['point_to'])) {
            $select->where('user.point <= ?', $criteria['point_to']);
        }
        
        // search by price (sum purchase)
        if (isset($criteria['sum_purchase_from']) AND strlen($criteria['sum_purchase_from'])) {
            $select->having('sum(if(point_log.point > point_after,point_log.point-point_after,0)) >= ?', $criteria['sum_purchase_from']);
        }
        if (isset($criteria['sum_purchase_to']) AND strlen($criteria['sum_purchase_to'])) {
            $select->having('sum(if(point_log.point > point_after,point_log.point-point_after,0)) <= ?', $criteria['sum_purchase_to']);
        }
        //echo $select; die;
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
    
    
}
