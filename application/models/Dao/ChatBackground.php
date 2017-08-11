<?php

/**
 * Dao_ChatBackground
 * 
 * @package Dao
 * @author duyld
 */
class Dao_ChatBackground extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable
{

    protected $_name = 'chat_background_master';
    
    protected $_rowClass = 'Dto_ChatBackground';
    
}
