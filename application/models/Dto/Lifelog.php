<?php

/**
 * Dto_Lifelog
 * 
 * @package Dto
 * @author ducdm
 */
class Dto_Lifelog extends Qsoft_Dto_Abstract
{
    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_Lifelog';
    
    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_STICKER = 'sticker';
    const TYPE_LOCATION = 'location';
    const TYPE_TEXT = 'text';
    
    /**
     * Return the array that contains the end user data
     * 
     * @return array
     */
    public function toEndUserArray()
    {
        $extracted = array('id', 'user_id', 'message', 'longitude', 'latitude','created_at', 'image_block' => 'blocked');
        $array = $this->toArray($extracted);
        
        // define the type of life log and return only the satisfy parameter
        $array['type'] = $this->getType();
        $array['sticker'] = $this->sticker;
        $array['video'] = $this->getVideoUrl();
        $array['image'] = $this->getImageUrl();
        
        // set total comments and likes to 0 if not defined
        if (isset($this->total_comments)) {
            $array['total_comments'] = (int) $this->total_comments;
        }
        if (isset($this->total_likes)) {
                $array['total_likes'] = (int) $this->total_likes;
        }
        
        // owner data
        if ( ! empty($this->user)) {
            $array = array_merge($array, $this->user->toContactArray());
        }
        
        return $array;
    }
    
    /**
     * Return the type of life log
     * @return string
     */
    public function getType()
    {
        if ( ! empty($this->sticker)) {
            return self::TYPE_STICKER;
        }
        
        if ( ! empty($this->video)) {
            return self::TYPE_VIDEO;
        }
        
        if ( ! empty($this->image)) {
            return self::TYPE_IMAGE;
        }
        
        if ( ! empty($this->longitude) OR ! empty($this->latitude)) {
            return self::TYPE_LOCATION;
        }
        
        return self::TYPE_TEXT;
    }
    
    /**
     * Check where current lifelog is imate type or not
     * 
     * @return boolean
     */
    public function isImageType()
    {
        return ($this->getType() === self::TYPE_IMAGE);
    }
    
    /**
     * Return the absolute url of lifelog image
     * 
     * @return string
     */
    public function getImageUrl()
    {
        $Url = Zend_Registry::get('app_config')->lifelog->image->large->url;
        return $this->_imageUrl($Url);
    }
    
    /**
     * Return the absolute url of lifelog video
     * 
     * @return string
     */
    public function getVideoUrl()
    {
        $Url = Zend_Registry::get('app_config')->lifelog->video->url;
        return $this->_videoUrl($Url);
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
     * Return the absolute url of video base on provided path
     * 
     * @param   string  $path
     * @return  string
     */
    protected function _videoUrl($path)
    {
        if (empty($this->video)) {
            return '';
        }
        
        return Qsoft_Helper_Url::generate($path . '/' . $this->video);
    }
    
    /**
     * Return file path of lifelog image
     * 
     * @return string
     */
    public function getImagePath()
    {
        $path = Zend_Registry::get('app_config')->lifelog->image->large->uploadPath;
        return $this->_imagePath($path);
    }
    
    /**
     * Return file path of lifelog video
     * 
     * @return string
     */
    public function getVideoPath()
    {
        $path = Zend_Registry::get('app_config')->lifelog->video->uploadPath;
        return $this->_videoPath($path);
    }
    
    /**
     * Return the file path of image base on provided path
     * 
     * @param   string  $path
     * @return  string
     */ 
    public function _imagePath($path)
    {
        if (empty($this->image)) {
            return '';
        }
        
        return Qsoft_Helper_File::getPath($path) . $this->image;
    }
    
    /**
     * Return the file path of video base on provided path
     * 
     * @param   string  $path
     * @return  string
     */ 
    public function _videoPath($path)
    {
        if (empty($this->video)) {
            return '';
        }
        
        return Qsoft_Helper_File::getPath($path) . $this->video;
    }
}