<?php

/**
 * Dto_AdminUser
 * 
 * @package Dto
 * @author duyld
 */
class Dto_AdminUser extends Qsoft_Dto_Abstract
{
    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_AdminUser';
    
    const ADMIN_STATE_VALID = 0;
    const ADMIN_STATE_INVALID = 1;
    
}