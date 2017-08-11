<?php

/**
 * Dao_Region
 * 
 * @package Dao
 * @author ducdm
 */
class Dao_Region extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable
{

    protected $_name = 'region';
    
    protected $_rowClass = 'Dto_Region';
    
}
