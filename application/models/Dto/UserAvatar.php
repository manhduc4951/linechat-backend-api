<?php

/**
 * Dto_UserAvatar
 * 
 * @package Dto
 * @author ducdm
 */
class Dto_UserAvatar extends Qsoft_Dto_Abstract
{
    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_UserAvatar';
    
    /**
     * Return the absolute url of user avatar image
     * 
     * @return string
     */
    public static function getImageUrl($avatar_img)
    {        
        return Qsoft_Helper_Url::generate(Zend_Registry::get('app_config')->user->avatar->url.$avatar_img);
    }
}