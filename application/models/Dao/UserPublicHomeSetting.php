<?php

/**
 * Dao_UserPublicHomeSetting
 * 
 * @package Dao
 * @author duyld
 */
class Dao_UserPublicHomeSetting extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable
{
    
    protected $_name = 'user_public_home_setting';
    
    protected $_rowClass = 'Dto_UserPublicHomeSetting';
    
}