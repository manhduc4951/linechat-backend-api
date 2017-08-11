<?php

/**
 * Dto_AclPermission
 * 
 * @package Dto
 * @author duyld
 */
class Dto_AclPermission extends Qsoft_Dto_Abstract
{
    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_AclPermission';
    
    const PERMISSION_NOT_ALLOWED = 0;
    const PERMISSION_ALLOWED = 1;
    
}