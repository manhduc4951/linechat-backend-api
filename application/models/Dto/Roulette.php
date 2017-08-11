<?php

/**
 * Dto_Roulette
 * 
 * @package Dto
 * @author ducdm
 */
class Dto_Roulette extends Qsoft_Dto_Abstract
{
    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_Roulette';
    
    const ROULETTE_TYPE_ITEM = 1;
    const ROULETTE_TYPE_GIFT = 2;
    const ROULETTE_TYPE_POINT = 3;
    
    
}