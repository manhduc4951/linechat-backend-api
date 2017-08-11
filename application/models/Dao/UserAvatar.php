<?php

/**
 * Dao_UserAvatar
 * 
 * @package Dao
 * @author ducdm
 */
class Dao_UserAvatar extends Qsoft_Db_Table_Abstract
    implements Dao_Interface_EntityAccessable
{
    
    protected $_name = 'avatar';
    
    protected $_rowClass = 'Dto_UserAvatar';
    
    public function countUserAvatar()
    {
        $select = $this->select()
                       ->setIntegrityCheck(false)                       
                       ->from('avatar', array('avatar_id'));
        
        return $this->fetchAll($select)->count();                
    }    
    
}
