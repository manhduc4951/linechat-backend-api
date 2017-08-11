<?php

/**
 * Business_Item class
 * 
 * @package LineChatApp
 * @subpackage Business
 * @author duyld
 */
class Business_Item
{
    
    const ITEM_SEARCH_DISTANCE = 'searchable_distance';
    
    /**
     * Item Dao
     * 
     * @var Dao_Item
     */
    protected $itemDao;
    
    /**
     * Item Usage Dao
     * 
     * @var Dao_ItemUsage
     */
    protected $itemUsageDao;
    
    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct()
    {        
        $this->itemDao = new Dao_Item();
        $this->itemUsageDao = new Dao_ItemUsage();
    }
    
    /**
     * Use an item
     * This method will check the usable of user for this item before perform
     * so you do not to check it by yourself
     * 
     * @param Dto_Item $itemDto
     * @param Dto_User $userDto
     * @return array
     */
    public function useItem(Dto_Item $itemDto, Dto_User $userDto)
    {
        // check whether user is allowed to use this item
        $itemUsageDto = $this->getItemUsage($userDto, $itemDto);
        if ( ! $this->isAllowedTo($userDto, $itemDto, $itemUsageDto)) {
            return array('status' => false, 'error_code' => ERROR_ITEM_EXPIRED);
        }
        
        // update the item usage
        $itemUsageDto->usage_count --;
        $this->itemUsageDao->update($itemUsageDto, 'usage_count');
        
        return array('status' => true);
    }
    
    /**
     * Check whether user is allowed to use this item
     * An item will be expired if usage count is reached the limit or
     * the expiry date is arrived
     * 
     * @param Dto_User $userDto
     * @param Dto_Item $itemDto
     * @return boolean
     */
    public function isAllowedTo(Dto_User $userDto, Dto_Item $itemDto)
    {
        // can pass the item usage dto as the third argument
        $itemUsageDto = func_get_arg(2);
        if (false === $itemUsageDto) {
            $itemUsageDto = $this->getItemUsage($userDto, $itemDto);
        }
        
        // check if the item usage is expired
        return ($itemUsageDto AND ! $this->isExpired($itemUsageDto, $itemDto));
    }
    
    /**
     * Check whether item is expired or not
     * An item will be expired if usage count is reached the limit or
     * the expiry date is arrived
     * 
     * @param Dto_ItemUsage $itemUsageDto
     * @param Dto_Item $itemDto
     * @return boolean
     */
    protected function isExpired(Dto_ItemUsage $itemUsageDto, Dto_Item $itemDto)
    {
        // check usage count
        if ($itemUsageDto->usage_count < 1) {
            return false;
        }
        
        // check expired date
        $timeLeft = Qsoft_Helper_Datetime::getRange(time(), $itemUsageDto->expired_date);
        if ($timeLeft < 0) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get the item usage dto
     * 
     * @param Dto_User $userDto
     * @param Dto_Item $itemDto
     * @return Dto_ItemUsage
     */
    public function getItemUsage(Dto_User $userDto, Dto_Item $itemDto)
    {
        return $this->itemUsageDao->fetchOneBy(array(
            'user_id' => $userDto->id,
            'item_name' => $itemDto->item_name,
        ));
    }
    
}