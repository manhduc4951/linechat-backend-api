<?php

/**
 * Dao_Item
 * 
 * @package Dao
 * @author ducdm
 */
class Dao_Item extends Qsoft_Db_Table_Abstract
    implements Dao_Interface_EntityAccessable
{
    
    protected $_name = 'item';
    
    protected $_rowClass = 'Dto_Item';
    
}
