<?php

/**
 * Dto_StickerPackage
 * 
 * @package Dto
 * @author sonvq
 */
class Dto_StickerPackage extends Qsoft_Dto_Abstract
{
    
    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_StickerPackage';
    
    /**
     * Return the array that contains the end user data
     * 
     * @return array
     */
    public function toEndUserArray()
    {
        $extracted = array('id', 'company', 'name', 'price', 'expired_at','description');
        $array = $this->toArray($extracted);
        
        $array['small_image'] = $this->getSmallImageUrl();
        $array['large_image'] = $this->getLargeImageUrl();
        $array['download'] = $this->getDownloadUrl();
        
        return $array;
    }
    
    /**
     * Return the absolute url of large image
     * 
     * @return string
     */
    public function getLargeImageUrl()
    {
        $largeUrl = Zend_Registry::get('app_config')->package->image->large->url;
        return $this->_imageUrl($largeUrl);
    }
    
    /**
     * Return the absolute url of small image
     * 
     * @return string
     */
    public function getSmallImageUrl()
    {
        $smallUrl = Zend_Registry::get('app_config')->package->image->small->url;
        return $this->_imageUrl($smallUrl);
    }
    
    /**
     * Return the absolute url of image base on provided path
     * 
     * @param   string  $path
     * @return  string
     */
    protected function _imageUrl($path)
    {
        if (empty($this->image)) {
            return '';
        }
        
        return Qsoft_Helper_Url::generate($path . '/' . $this->image);
    }

    /**
     * Return the absolute url of downloadable zipped sticker package
     * 
     * @return string
     */
    public function getDownloadUrl()
    {
        $downloadUrl = Zend_Registry::get('app_config')->package->upload->url;
        return Qsoft_Helper_Url::generate($downloadUrl . '/' . $this->id . '.zip');
    }
    
}