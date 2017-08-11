<?php

/**
 * Dto_Item
 * 
 * @package Dto
 * @author ducdm
 */
class Dto_Item extends Qsoft_Dto_Abstract
{
    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_Item';
    
    const ITEM_TYPE_SEARCH = 'search';
    const ITEM_TYPE_IMMEDIATE = 'immediate';
    const ITEM_TYPE_SHAKE = 'shake';
    const ITEM_TYPE_PROFILE = 'profile';
    const ITEM_TYPE_TALK_LOG = 'talk_log';
    
    /**
     * Return the absolute url of item image
     * 
     * @return string
     */
    public static function getImageUrl($item_img)
    {        
        return Qsoft_Helper_Url::generate(Zend_Registry::get('app_config')->item->image->url.$item_img);
    }
    
}