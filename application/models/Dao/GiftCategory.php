<?php

/**
 * Dao_GiftCategory
 * 
 * @package Dao
 * @author ducdm
 */
class Dao_GiftCategory extends Qsoft_Db_Table_Abstract
    implements Dao_Interface_EntityAccessable
{
    
    protected $_name = 'gift_category';
    
    protected $_rowClass = 'Dto_GiftCategory';
    
}
