<?php

/**
 * Dao_UserImage
 * 
 * @package Dao
 * @author ducdm
 */
class Dao_UserImage extends Qsoft_Db_Table_Abstract
    implements Dao_Interface_EntityAccessable
{
    
    protected $_name = 'user_img';
    
    protected $_rowClass = 'Dto_UserImage';
    
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
                ->joinLeft('user', 'user_img.user_id = user.id',
                  array('user_id AS user_id_name', 'nick_name', 'profile_user_img_id', 'updated_at AS user_updated_at'));             
        }
        
        // Search user by user_id
        
        if (isset($criteria['user_id']) AND strlen($criteria['user_id'])) {        
            $select->where("user.user_id LIKE ?", "%{$criteria['user_id']}%");
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
