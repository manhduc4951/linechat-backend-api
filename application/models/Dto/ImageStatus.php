<?php

/**
 * Dto_ImageStatus
 * 
 * @package Dto
 * @author ducdm
 */
class Dto_ImageStatus extends Qsoft_Dto_Abstract
{
    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_ImageStatus';
    
    const TYPE_USER = 'user';
    const TYPE_LIFELOG = 'lifelog';
    const TYPE_FILE_TRANSFER = 'file_transfer';
    
}