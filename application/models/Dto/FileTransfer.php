<?php

/**
 * Dto_FileTransfer
 * 
 * @package Dto
 * @author duyld
 */
class Dto_FileTransfer extends Qsoft_Dto_Abstract
{
    
    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_FileTransfer';
    
    const USER_LIST_DELIMITER = ';';
    
    /**
     * Does this file is blocked form download
     * 
     * @return boolean
     */
    public function isBlocked()
    {
        return (boolean) $this->file_block;
    }
    
    /**
     * Return the full path of file
     * 
     * @return  string
     */
    public function getFilePath()
    {
        $uploadPath = Zend_Registry::get('app_config')->user->file->uploadPath;
        return Qsoft_Helper_File::getPath($uploadPath) . $this->file_name;
    }
    
    /**
     * Return the url of thumbnail image
     * 
     * @return  string
     */
    public function getThumbnailUrl()
    {
    	$config = Zend_Registry::get('app_config')->user->file->thumbnail;
        $filePath = Qsoft_Helper_File::getPath($config->uploadPath) . $this->file_name;
        
        if (file_exists($filePath)) {
            return Qsoft_Helper_Url::generate($config->url) . '/' . $this->file_name;
        }
        
        if (file_exists($filePath . Qsoft_Filter_File_ImageThumbnail::VIDEO_THUMBNAIL_EXTENSION)) {
        	return Qsoft_Helper_Url::generate($config->url) . '/' . $this->file_name . Qsoft_Filter_File_ImageThumbnail::VIDEO_THUMBNAIL_EXTENSION;
        }
        
        return '';
    }
    
    /**
     * Check whether this file has thumbnail image or not
     * 
     * @return boolean
     */
    public function hasThumbnail()
    {
        $config = Zend_Registry::get('app_config')->user->file->thumbnail;
        $filePath = Qsoft_Helper_File::getPath($config->uploadPath) . $this->file_name;
        
        return (
            file_exists($filePath) OR
            file_exists($filePath . Qsoft_Filter_File_ImageThumbnail::VIDEO_THUMBNAIL_EXTENSION)
        );
    }
}