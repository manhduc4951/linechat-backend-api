<?php

/**
 * Dao_PointConfig
 * 
 * @package Dao
 * @author ducdm
 */
class Dao_PointConfig extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable
{

    protected $_name = 'point_config';
    
    protected $_rowClass = 'Dto_PointConfig';    
    
}
