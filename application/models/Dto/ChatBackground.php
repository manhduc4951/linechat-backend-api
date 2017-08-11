<?php

/**
 * Dto_ChatBackground
 * 
 * @package Dto
 * @author duyld
 */
class Dto_ChatBackground extends Qsoft_Dto_Abstract
{
    
    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_ChatBackground';
    
    /**
     * Return the array that contains the end user data
     * 
     * @return array
     */
    public function toEndUserArray()
    {
        $array = $this->toArray('id');

        $array['small_image'] = $this->getSmallImageUrl();
        $array['large_image'] = $this->getLargeImageUrl();

        return $array;
    }
    
     /**
     * Return the absolute url of large image
     * 
     * @return string
     */
    public function getLargeImageUrl()
    {
        return self::largeImageUrl($this->image);
    }

    /**
     * Return the absolute url of small image
     * 
     * @return string
     */
    public function getSmallImageUrl()
    {
        return self::smallImageUrl($this->image);
    }
    
    /**
     * Return the absolute url for small image
     * 
     * @return string
     */
    public static function smallImageUrl($image)
    {
        if ( ! $image) {
            return '';
        }
        
        $url = Zend_Registry::get('app_config')->chatBackground->small->url;
        return Qsoft_Helper_Url::generate($url . '/' . $image);
    }
    
    /**
     * Return the absolute url for large image
     * 
     * @return string
     */
    public static function largeImageUrl($image)
    {
        if ( ! $image) {
            return '';
        }
        
        $url = Zend_Registry::get('app_config')->chatBackground->large->url;
        return Qsoft_Helper_Url::generate($url . '/' . $image);
    }
}