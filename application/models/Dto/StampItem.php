<?php

/**
 * Dto_StampItem
 * 
 * @package Dto
 * @author ducdm
 */
class Dto_StampItem extends Qsoft_Dto_Abstract
{
    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_StampItem';
    
    /**
     * Stamp image types
     */
    const STAMP_TYPE_ANIMATION = 'gif';
    const STAMP_TYPE_NORMAL = 'normal';
    
    /**
     * Check whether this stamp is animation or not
     * 
     * @return boolean
     */
    public function isAnimation()
    {
        return $this->stamp_item_type == self::STAMP_TYPE_ANIMATION;
    }
    
    /**
     * Check whether this stamp is normal or not
     * 
     * @return boolean
     */
    public function isNormal()
    {
        return $this->stamp_item_type == self::STAMP_TYPE_NORMAL;
    }
    
    /**
     * Set the stamp item type
     * If type is not provided, auto define depends on current item length
     * 
     * @param string $type
     * @return Dto_StampItem
     */
    public function setStampType($type = null)
    {
        $this->stamp_item_type =  $type ?:
            ($this->stamp_item_length > 1) ? self::STAMP_TYPE_ANIMATION : self::STAMP_TYPE_NORMAL;
        
        return $this;
    }
    
    /**
     * Auto detect the stamp type depends on current item length
     * 
     * @return Dto_StampItem
     */
    public function detectStampType()
    {
        return $this->setStampType();
    }
    
    /**
     * Convert to array that contains end user data only
     * 
     * @return array
     */
    public function toEndUserArray()
    {
        $extracted = array('stamp_item_id', 'stamp_item_file_name', 'stamp_item_type', 'stamp_item_length');
        return $this->toArray($extracted);
    }
    
}