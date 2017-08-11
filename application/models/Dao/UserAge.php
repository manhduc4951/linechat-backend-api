<?php

/**
 * Dao_UserAge
 * 
 * @package Dao
 * @author ducdm
 */
class Dao_UserAge extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable
{

    protected $_name = 'user_age';
    
    protected $_rowClass = 'Dto_UserAge';
    
}
