<?php

/**
 * Dto_UserGroup
 * 
 * @package Dto
 * @author sonvq
 */
class Dto_UserGroup extends Qsoft_Dto_Abstract
{
    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_UserGroup';
    
    /**
     * Is group auto approved?
     * 
     * @return boolean
     */
    public function isAutoApprove()
    {
        return ($this->is_auto_approve > 0);
    }
    
    /**
     * Return the array that contains the end user data
     * 
     * @return array
     */
    public function toEndUserArray()
    {
        $extracted = array('node_id' => 'id', 'unique_id' => 'owner_id', 'name', 'description', 'is_auto_approve');
        $array = $this->toArray($extracted);
        
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
        $largeUrl = Zend_Registry::get('app_config')->group->image->large->url;
        return $this->_imageUrl($largeUrl);
    }
    
    /**
     * Return the absolute url of small image
     * 
     * @return string
     */
    public function getSmallImageUrl()
    {
        $smallUrl = Zend_Registry::get('app_config')->group->image->small->url;
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
     * Return file path of large image
     * 
     * @return string
     */
    public function getLargeImagePath()
    {
        $largePath = Zend_Registry::get('app_config')->group->image->large->uploadPath;
        return $this->_imagePath($largePath);
    }
    
    /**
     * Return file path of small image
     * 
     * @return string
     */
    public function getSmallImagePath()
    {
        $smallPath = Zend_Registry::get('app_config')->group->image->small->uploadPath;
        return $this->_imagePath($smallPath);
    }
    
    /**
     * Return the file path of image base on provided path
     * 
     * @param   string  $path
     * @return  string
     */
    protected function _imagePath($path)
    {
        if (empty($this->image)) {
            return '';
        }
        
        return Qsoft_Helper_File::getPath($path) . $this->image;
    }

}