<?php

/**
 * Dao_LifelogComment
 * 
 * @package Dao
 * @author ducdm
 */
class Dao_LifelogComment extends Qsoft_Db_Table_Abstract
    implements Dao_Interface_EntityAccessable
{

    protected $_name = 'lifelog_comment';
    
    protected $_rowClass = 'Dto_LifelogComment';
    
    /**
     * Retrieve a collection of instance by provided name and value with user information also
     * 
     * @param   string|array    $name
     * @param   string|array    $value
     * @return  array
     */
    public function fetchAllWithUserBy($name, $value)
    {
        $select = $this->select()
            ->setIntegrityCheck(false)
            ->from($this->_name)
            
            // collect some required user info
            ->joinInner(
                'user',
                'lifelog_comment.user_id = user.id AND user.state != :state_delete',
                array('nick_name', 'unique_id', 'user_id')
            )
            
            // join user image
            ->joinLeft(
                'user_img',
                'user.profile_user_img_id = user_img.user_img_id',
                array('user_img')
            )
            
            ->bind(array(
                'state_delete' => Dto_User::STATE_DELETE,
            ))
        ;
        
        $this->addMixedWhere($select, $name, $value);
        
        return $this->getAdapter()->fetchAssoc($select);               
            
    }
}
