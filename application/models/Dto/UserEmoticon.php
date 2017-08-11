<?php

/**
 * Dto_UserEmoticon
 * 
 * @package Dto
 * @author ducdm
 */
class Dto_UserEmoticon extends Qsoft_Dto_Abstract
{
    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_UserEmoticon';
    
    /**
     * Return the absolute url of user emoticon image
     * 
     * @return string
     */
    public static function getImageUrl($emoticon_img)
    {        
        return Qsoft_Helper_Url::generate(Zend_Registry::get('app_config')->user->emoticon->url.$emoticon_img);
    }
    
    public static function getImagePath($emoticon_img)
    {
        return Qsoft_Helper_Url::generate(Zend_Registry::get('app_config')->user->emoticon->uploadPath.$emoticon_img);    
    }   
}