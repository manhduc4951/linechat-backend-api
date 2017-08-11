<?php

/**
 * Dao_ItemUsage
 * 
 * @package Dao
 * @author đucm
 */
class Dao_ItemUsage extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable
{
    
    protected $_name = 'user_item';
    
    protected $_rowClass = 'Dto_ItemUsage';
    
}
