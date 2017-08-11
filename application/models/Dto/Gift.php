<?php

/**
 * Dto_Gift
 * 
 * @package Dto
 * @author ducdm
 */
class Dto_Gift extends Qsoft_Dto_Abstract
{
    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_Gift';
    
    /**
     * Return the absolute url of gift image
     * 
     * @return string
     */
    public static function getImageUrl($gift_img)
    {        
        return Qsoft_Helper_Url::generate(Zend_Registry::get('app_config')->gift->image->url.$gift_img);
    }
    
}