<?php

/**
 * Dao_AclAction
 * 
 * @package Dao
 * @author duyld
 */
class Dao_AclAction extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable
{
    
    protected $_name = 'acl_action';
    
    protected $_rowClass = 'Dto_AclAction';
    
}