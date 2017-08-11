<?php

/**
 * Dao_Lifelog
 * 
 * @package Dao
 * @author ducdm
 */
class Dao_Lifelog extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable
{

    protected $_name = 'lifelog';
    
    protected $_rowClass = 'Dto_Lifelog';
    
    /**
     * Retrieve all lifelogs by user have some subscriptions status with current user
     * 
     * @param string $uniqueId
     * @param integer $beginLifelogId
     * @param array $subscriptions List of subscription status for retrieve
     * @param string|array $order
     * @param integer $count
     * @param integer $offset
     * @return Qsoft_Db_Table_Rowset
     */
    public function fetchAllBySubscriptionStatus($uniqueId, $beginLifelogId,
        array $subscriptions = array(), $order = null, $count = null, $offset = null)
    {
        if (null === $order) {
            $order = 'lifelog.id DESC';
        }
        
        $select = $this->select()
            ->setIntegrityCheck(false)
            ->from('lifelog')
            // collect some required user info
            ->joinInner(
                'user',
                'lifelog.user_id = user.id AND user.state != :state_delete',
                array('nick_name', 'unique_id', 'user_id')
            )
            
            // join roster to detect who is friend
            ->joinLeft(
                'ofRoster',
                'ofRoster.username = :unique_id AND
                    ofRoster.jid = CONCAT(user.unique_id, :domain) AND
                    ofRoster.sub IN (' . implode(',', $subscriptions) . ')
                ',
                null
            )
            
            // join user image
            ->joinLeft(
                'user_img',
                'user.profile_user_img_id = user_img.user_img_id',
                array('user_img')
            )
            
            ->joinLeft('lifelog_comment',
                'lifelog.id = lifelog_comment.lifelog_id',
                 array('total_comments' => new Zend_Db_Expr('COUNT(DISTINCT(lifelog_comment.id))'))
            )
            
            ->joinLeft('lifelog_like',
                'lifelog.id = lifelog_like.lifelog_id',
                 array('total_likes' => new Zend_Db_Expr('COUNT(DISTINCT(lifelog_like.id))'))
            )
            
            // another settings
            ->where('ofRoster.rosterID IS NOT NULL OR user.unique_id = :unique_id')            
            ->order($order)
            ->limit($count, $offset)
            ->group('lifelog.id')
            ->bind(array(
                'unique_id' => $uniqueId,
                'domain' => '@' . Zend_Registry::get('xmpp_config')->xmpp->domain,
                'state_delete' => Dto_User::STATE_DELETE,
            ))
        ;
            
        if ($beginLifelogId > 0) {
            $select->where('lifelog.id < ?', $beginLifelogId);
        }
        
        $rows = $this->getAdapter()->fetchAssoc($select);
        return $this->initRowsetFromArray($rows);
    }
   
    /**
     * Get one lifelog with number of comment and like
     * 
     * @param int $id   Lifelog ID
     * @return  Dto_Lifelog
     */
    public function fetchOneWithUser($id)
    {
        $select = $this->select()            
            ->from($this->_name)
            ->setIntegrityCheck(false)
            
            // collect some required user info
            ->joinInner(
                'user',
                'lifelog.user_id = user.id AND user.state != :state_delete',
                array('nick_name', 'unique_id', 'user_id')
            )
            
            // join user image
            ->joinLeft(
                'user_img',
                'user.profile_user_img_id = user_img.user_img_id',
                array('user_img')
            )
            
            ->joinLeft('lifelog_comment',
                        'lifelog.id = lifelog_comment.lifelog_id',
                         array('total_comments' => new Zend_Db_Expr('COUNT(DISTINCT(lifelog_comment.id))')))
            ->joinLeft('lifelog_like',
                        'lifelog.id = lifelog_like.lifelog_id',
                         array('total_likes' => new Zend_Db_Expr('COUNT(DISTINCT(lifelog_like.id))')))
            ->where('lifelog.id = ?', $id)
            ->group('lifelog.id')
            ->bind(array(
                'state_delete' => Dto_User::STATE_DELETE,
            ))
        ;
        
        $rows = $this->getAdapter()->fetchAssoc($select);
        if ($rows) {
            return $this->initDtoFormArray(current($rows));
        }
        
        return null;
    }
    
    /**
     * Get all lifelog posted by an user
     * 
     * @param int $id   User ID
     * @param string|array $order
     * @param integer $count
     * @param integer $offset
     * @return An array of Dto_Lifelog
     */
    public function fetchAllByUser($id, $order = null, $count = null, $offset = null)
    {
        if (null === $order) {
            $order = 'lifelog.id DESC';
        }
        
        $select = $this->select()            
            ->from($this->_name)
            ->setIntegrityCheck(false)
            // join user table to collect user information
            ->joinInner(
                'user',
                'lifelog.user_id = user.id AND user.state != :state_delete AND user.id = :user_id',
                array('nick_name', 'unique_id', 'user_id')
            )
            // join user image
            ->joinLeft(
                'user_img',
                'user.profile_user_img_id = user_img.user_img_id',
                array('user_img')
            )
            // counting total comments
            ->joinLeft('lifelog_comment',
                        'lifelog.id = lifelog_comment.lifelog_id',
                         array('total_comments' => new Zend_Db_Expr('COUNT(DISTINCT(lifelog_comment.id))')))
            // counting total comments
            ->joinLeft('lifelog_like',
                        'lifelog.id = lifelog_like.lifelog_id',
                         array('total_likes' => new Zend_Db_Expr('COUNT(DISTINCT(lifelog_like.id))')))
            ->bind(array(
                'user_id' => $id,
                'state_delete' => Dto_User::STATE_DELETE,
            ))
            ->order($order)
            ->limit($count, $offset)
            ->group('lifelog.id')
        ;
        
        $rows = $this->getAdapter()->fetchAssoc($select);
        return $this->initRowsetFromArray($rows);
    }
    
    /**
     * Init rowset from array with user and user image data beside lifelog Dto
     * 
     * @param array $rows
     * @return Qsoft_Db_Table_Rowset
     */
    protected function initRowsetFromArray(array $rows)
    {
        $data = array();
        // loop through all rows
        foreach ($rows as $row) {
            $data[] = $this->initDtoFormArray($row);
        }
        
        return new Qsoft_Db_Table_Rowset(array('data' => $data, 'table' => $this, 'rowClass' => 'Dto_Lifelog'));
    }
    
    /**
     * Init Dto from array with user and user image data beside lifelog Dto
     * 
     * @param array $data
     * @return Dto_Lifelog
     */
    protected function initDtoFormArray(array $data)
    {
        // lifelog dto
        $lifelog = new Dto_Lifelog();
        $lifelog->setFromArray($data);
        isset($data['total_comments']) AND $lifelog->addColumn('total_comments', $data['total_comments']);
        isset($data['total_likes']) AND $lifelog->addColumn('total_likes', $data['total_likes']);
    
        // init user dto
        $user = new Dto_User();
        $user->setFromArray($data);
        $user->addColumn('user_img', $data['user_img']);
    
        // finish
        $lifelog->addColumn('user', $user);
        return $lifelog;
    }
    
    /**
     * Mark all images as deleted
     * 
     * @param mixed $idArray
     * @return void
     */
    public function dontDisplayImage($idArray = array())
    {
        if (!is_array($idArray)) {
            $idArray = array($idArray);
        }

        if ($idArray) {
            $where = $this->getAdapter()->quoteInto('id IN (?)', $idArray);
            $this->getAdapter()->update($this->_name, array('image_block' => 1), $where);
        }
    }
    
}
