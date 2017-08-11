<?php

/**
 * Dao_WordNotGood
 * 
 * @package Dao
 * @author ducdm
 */
class Dao_WordNotGood extends Qsoft_Db_Table_Abstract
    implements Dao_Interface_EntityAccessable
{

    protected $_name = 'ng_word';
    
    protected $_rowClass = 'Dto_WordNotGood';    
       
}
