<?php

/**
 * Dao_Chat_MessageHistory
 * 
 * @package Dao_Chat
 * @author ducdm
 */
class Dao_Chat_MessageHistory extends Qsoft_Db_Table_Abstract
{

    protected $_name = 'ofMessageArchive';
    
    protected $_rowClass = 'Dto_Chat_MessageHistory';
    
    protected $_primary = 'conversationID';
    
    public function doFilter(array $criteria = array(), Zend_Db_Select $select = null)
	{
		if ( ! $select instanceof Zend_Db_Select) {
			$select = $this->select()
                           ->setIntegrityCheck(false)
                           ->from('ofMessageArchive')
                           ->joinLeft('user','ofMessageArchive.fromJID = CONCAT(user.unique_id, :domain) AND user.state != :state_delete', array('user_id AS user_id_sender', 'nick_name AS nick_name_sender', 'avatar_id AS avatar_id_sender', 'unique_id AS unique_id_sender', 'valuation as valuation_sender'))                           
                           ->joinLeft('user AS user2','ofMessageArchive.toJID = CONCAT(user2.unique_id, :domain) AND user2.state != :state_delete', array('user2.user_id AS user_id_receiver', 'user2.nick_name AS nick_name_receiver', 'user2.avatar_id AS avatar_id_receiver', 'user2.unique_id AS unique_id_receiver', 'user2.valuation AS valuation_receiver'))
                           ->joinLeft('avatar','user.avatar_id = avatar.avatar_id', array('avatar_img as avatar_img_sender'))
                           ->joinLeft('avatar AS avatar2','user2.avatar_id = avatar2.avatar_id', array('avatar2.avatar_img as avatar_img_receiver'))
                           ->joinLeft('region', 'user.pref = region.id', array('region_name AS user_sender_pref'))
                           ->joinLeft('user_age', 'user.age = user_age.id', array('age AS user_sender_age'))
                           ->joinLeft('user_age AS user_age2', 'user2.age = user_age2.id', array('age AS user_receiver_age'))
                           ->bind(array(                                
                                'domain' => '@' . Zend_Registry::get('xmpp_config')->xmpp->domain,
                                'state_delete' => Dto_User::STATE_DELETE,
                           ))
                           ->order('conversationID DESC')
                           ; 
		
        }
        
        // Search user by unique_id
		if (isset($criteria['body']) AND strlen($criteria['body'])) {
            $select->where("body LIKE ?", "%{$criteria['body']}%");
        }
        // Search user by user_id
		if (isset($criteria['user_id']) AND strlen($criteria['user_id'])) {
            $select->where("user.user_id LIKE ? OR user2.user_id LIKE ?", "%{$criteria['user_id']}%");
                   
        }
        // Search user by nick_name
		if (isset($criteria['nick_name']) AND strlen($criteria['nick_name'])) {
            $select->where("user.nick_name LIKE ? OR user2.nick_name LIKE ?", "%{$criteria['nick_name']}%");
                   
        }
        // Search user by group_name
		if (isset($criteria['group_name']) AND strlen($criteria['group_name'])) {
            $select->where("toJID LIKE ? ", "%{$criteria['group_name']}%");
                   
        }
        // Search user by avatar_id
        if (!empty($criteria['avatar_id'])) {
            $select->where("user.avatar_id IN (?)", $criteria['avatar_id']);
        }
        
        // Search user by pref (region)
		if (isset($criteria['pref']) and strlen($criteria['pref'])) {
			$select->where('user.pref = ?', $criteria['pref']);
		}
        
        // Search user by age
		if (isset($criteria['age']) and strlen($criteria['age'])) {
			$select->where('user.age = ?', $criteria['age']);
		}
        
        // search by valuation
        if (!empty($criteria['valuation'])) {
            $select->where("user.valuation IN (?) or user2.valuation IN (?)", $criteria['valuation']);
        }
        // search by registration date
        if (isset($criteria['sentDate_from']) AND strlen($criteria['sentDate_from'])) {
            $select->where('DATE_FORMAT(FROM_UNIXTIME(sentDate/1000), \'%Y/%m/%d\') >= ?', $criteria['sentDate_from']);
        }
        if (isset($criteria['sentDate_to']) AND strlen($criteria['sentDate_to'])) {
            $select->where('DATE_FORMAT(FROM_UNIXTIME(sentDate/1000), \'%Y/%m/%d\') <= ?', $criteria['sentDate_to']);
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
