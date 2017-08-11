<?php

/**
 * Dto_Stamp
 * 
 * @package Dto
 * @author ducdm
 */
class Dto_Stamp extends Qsoft_Dto_Abstract
{
    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_Stamp';
    
    /**
     * Convert to array that contains end user data only
     * 
     * @return array
     */
    public function toEndUserArray()
    {
        $extracted = array('stamp_id', 'stamp_name', 'stamp_description', 'point');
        $array = $this->toArray($extracted);
        
        $array['icon_image'] = $this->getIconImageUrl();
        $array['small_image'] = $this->getSmallImageUrl();
        $array['large_image'] = $this->getLargeImageUrl();
        
        if (isset($this->purchased)) {
            $array['purchased'] = $this->purchased;
        }
        
        return $array;
    }
    
    /**
     * Return the absolute url of icon image
     * 
     * @return string
     */
    public function getIconImageUrl()
    {
        if (empty($this->stamp_icon)) {
            return '';
        }
        
        $url = Zend_Registry::get('app_config')->stamp->image->icon->url;
        return Qsoft_Helper_Url::generate($url . '/' . $this->stamp_icon);
    }
    
    /**
     * Return the absolute url of small image
     * 
     * @return string
     */
    public function getSmallImageUrl()
    {
        if (empty($this->stamp_small_image)) {
            return '';
        }
        
        $smallUrl = Zend_Registry::get('app_config')->stamp->image->small->url;
        return Qsoft_Helper_Url::generate($smallUrl . '/' . $this->stamp_small_image);
    }

    /**
     * Return the absolute url of large image
     * 
     * @return string
     */
    public function getLargeImageUrl()
    {
        if (empty($this->stamp_large_image)) {
            return '';
        }
        
        $largeUrl = Zend_Registry::get('app_config')->stamp->image->large->url;
        return Qsoft_Helper_Url::generate($largeUrl . '/' . $this->stamp_large_image);
    }
    
    public function getSmallImagePath($name = null)
    {
        if(!$name) $name = $this->stamp_small_image;
        return Zend_Registry::get('app_config')->stamp->image->small->uploadPath.$name;         
    }
    
    public function getLargeImagePath($name = null)
    {
        if(!$name) $name = $this->stamp_large_image;
        return Zend_Registry::get('app_config')->stamp->image->large->uploadPath.$name;         
    }
    
    public function getZipPath($name = null)
    {
        if(!$name) $name = $this->stamp_zip_package;
        if ($name) {
            return Zend_Registry::get('app_config')->stamp->zip->uploadPath.$name;    
        }
        return '';
                 
    }
    
}