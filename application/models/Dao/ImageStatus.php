<?php

/**
 * Dao_ImageStatus
 * 
 * @package Dao
 * @author duyld
 */
class Dao_ImageStatus extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable
{

    protected $_name = 'img_status';
    
    protected $_rowClass = 'Dto_ImageStatus';
    
    /**
     * Generate filter query to display in the manage image
     * 
     * @param   array 				$criteria
	 * @param	null|Zend_Db_Select	$select
     * @return  Zend_Db_Select
     */
    public function doFilter(array $criteria = array(), Zend_Db_Select $select = null)
    {
        if ( ! $select instanceof Zend_Db_Select) {
            $select = $this->select()
                ->from($this->_name)
                ->setIntegrityCheck(false)
                ->joinLeft('user', 'img_status.user_id = user.id', array('user.user_id AS user_id_name', 'nick_name', 'profile_user_img_id'))
                ->joinLeft('user_img', 'img_status.user_img_id = user_img.user_img_id', array('user_img.user_img AS user_image'))
                ->joinLeft('lifelog', 'img_status.lifelog_id = lifelog.id', array('lifelog.id AS lifelog_id', 'lifelog.image AS lifelog_image', 'lifelog.image_block AS lifelog_image_block'))
                ->joinLeft('file_transfer', 'img_status.file_transfer_id = file_transfer.id', array('file_transfer.id AS file_transfer_id', 'file_transfer.file_name AS file_transfer_image', 'file_transfer.file_block AS file_transfer_image_block'))
                ->order('created_at DESC')
                ; 
                              
        }
        
        // Search user by user_id
        
        if (isset($criteria['user_id']) AND strlen($criteria['user_id'])) {        
            $select->where("user.user_id LIKE ?", "%{$criteria['user_id']}%");
        }
        
        // search by type
        if (!empty($criteria['type'])) {
            $select->where("type IN (?)", $criteria['type']);
        }
        
        // search by registration date
        if (isset($criteria['created_at_from']) AND strlen($criteria['created_at_from'])) {
            $select->where('DATE_FORMAT(img_status.created_at, \'%Y/%m/%d\') >= ?', $criteria['created_at_from']);
        }
        if (isset($criteria['created_at_to']) AND strlen($criteria['created_at_to'])) {
            $select->where('DATE_FORMAT(img_status.created_at, \'%Y/%m/%d\') <= ?', $criteria['created_at_to']);
        }
        //echo $select;die;
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
