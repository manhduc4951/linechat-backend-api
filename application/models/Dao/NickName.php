<?php

/**
 * Dao_NickName
 * 
 * @package Dao
 * @author duyld
 */
class Dao_NickName extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable
{

    protected $_name = 'nickname';
    
    protected $_rowClass = 'Dto_NickName';
    
    /**
     * Return a random nick name
     * 
     * @return Dto_NickName
     */
    public function fetchRandom()
    {
        return $this->fetchRow(null, new Zend_Db_Expr('RAND()'));
    }
    
}
