<?php

/**
 * Dao_AclController
 * 
 * @package Dao
 * @author duyld
 */
class Dao_AclController extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable
{
    
    protected $_name = 'acl_controller';
    
    protected $_rowClass = 'Dto_AclController';
    
}