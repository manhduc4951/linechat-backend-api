<?php

/**
 * Dao_Dice
 * 
 * @package Dao
 * @author đucm
 */
class Dao_Dice extends Qsoft_Db_Table_Abstract
    implements Dao_Interface_EntityAccessable
{
    
    protected $_name = 'dice';
    
    protected $_rowClass = 'Dto_Dice';
    
}
