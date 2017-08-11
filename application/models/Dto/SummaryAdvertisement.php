<?php

/**
 * Dto_SummaryAdvertisement
 * 
 * @package Dto
 * @author ducdm
 */
class Dto_SummaryAdvertisement extends Qsoft_Dto_Abstract
{
    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_SummaryAdvertisement';
    
    const MEDIA_GROUP_PAGE = 1;
    
    const MEDIA_GROUP_WEB = 2;
    
    const MEDIA_GROUP_AD = 3;
    
}