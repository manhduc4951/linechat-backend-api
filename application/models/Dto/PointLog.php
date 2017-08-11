<?php

/**
 * Dto_PointLog
 * 
 * @package Dto
 * @author ducdm
 */
class Dto_PointLog extends Qsoft_Dto_Abstract
{
    
    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_PointLog';
    
    const POINT_LOG_INCREASE = 2;
    
    const POINT_LOG_DECREASE = 1;
    
    public function detectPointLogType()
    {
        if($this->type == self::POINT_LOG_INCREASE) {
            return 'Increase';
        } elseif($this->type == self::POINT_LOG_DECREASE) {
            return 'Decrease';
        } else {
            return 'Unknown';
        }
    }
    
}