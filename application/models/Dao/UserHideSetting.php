<?php

/**
 * Dao_UserHideSetting
 * 
 * @package Dao
 * @author duyld
 */
class Dao_UserHideSetting extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable
{
    
    protected $_name = 'user_hide_setting';
    
    protected $_rowClass = 'Dto_UserHideSetting';
    
}