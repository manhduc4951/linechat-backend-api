<?php

/**
 * Dto_UserImage
 * 
 * @package Dto
 * @author ducdm
 */
class Dto_UserImage extends Qsoft_Dto_Abstract
{
    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_UserImage';
    
    /**
     * Return the absolute url of small image
     * 
     * @return string
     */
    public function getSmallImageUrl()
    {
        $smallUrl = Zend_Registry::get('app_config')->user->image->small->url;
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
        if (empty($this->user_img)) {
            return '';
        }

        return Qsoft_Helper_Url::generate($path . '/' . $this->user_img);
    }
    
}