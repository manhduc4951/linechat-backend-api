<?php

class RouletteController extends Qsoft_Controller_Backend_Action
{ 
    protected $_daoClass = 'Dao_Roulette';
    
    protected $_formClass = 'Form_Roulette';
    
    protected $_businessClass = 'Business_Roulette';
	
	/**
     * Initialize method
     */
    public function init()
    {
        parent::init();
        
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/modules/backend/configs/navigation.ini', 'settings_nav');
        $this->view->secondNavigation = new Zend_Navigation($config);
    }
	
	/**
     * Get the Dao object
     * 
     * @return Dao_Roulette
     */
	public function getDao()
	{
		return parent::getDao();
	}
    
    /**
     * Get the business model
     * 
     * @return Business_Image
     */
	public function getBusiness()
	{
		return parent::getBusiness();
	}
    
    public function indexAction()
    {
		die('under construction!. todo in application\modules\backend\controllers\RouletteController.php');
        if($this->_request->isPost())
        {
            $this->getBusiness()->updateRoulette($this->_request->getPost());    
        }
        
        $itemTypes = array(Dto_Item::ITEM_TYPE_SEARCH,
                           Dto_Item::ITEM_TYPE_IMMEDIATE,
                           Dto_Item::ITEM_TYPE_SHAKE,
                           Dto_Item::ITEM_TYPE_PROFILE,
                           Dto_Item::ITEM_TYPE_TALK_LOG,
                          );
        $this->view->items_type = $itemTypes;
        
        $giftCategoryDao = new Dao_GiftCategory();
        $this->view->gift_categories = $giftCategoryDao->fetchAll();
        
        $giftDao = new Dao_Gift();
        
        $itemDao = new Dao_Item();        
        
        // get gift_category with gift
        foreach($giftCategoryDao->fetchAll() as $key=>$giftCategory) {
            
            $arrayGiftAndGiftCat[] = $giftCategory->gift_category_id;
        }
        $arrayGiftAndGiftCatFlip = array_flip($arrayGiftAndGiftCat);
        
        foreach($arrayGiftAndGiftCatFlip as $key=>$value)
        {
            $arrayGift = $giftDao->fetchAllBy('gift_category_id',$key)->toArray();
            if(count($arrayGift) > 0) {
                $gift_category_has_value[] = $key;
            }        
            $arrayGiftAndGiftCatFlip[$key] = $arrayGift;            
               
        }
        // get item_type with item
        $arrayItemAndItemTypeFlip = array_flip($itemTypes);
        
        foreach($arrayItemAndItemTypeFlip as $key=>$value)
        {
            $arrayItem = $itemDao->fetchAllBy('item_type',$key)->toArray();            
            if(count($arrayItem) > 0) {
                $item_type_has_value[] = $key;
            }
            $arrayItemAndItemTypeFlip[$key] = $arrayItem;
               
        }
        
        $this->view->array_gift_giftcategory = $arrayGiftAndGiftCatFlip;   
        $this->view->array_item_itemtype = $arrayItemAndItemTypeFlip; 
        $this->view->item_type_has_value = $item_type_has_value;
        $this->view->gift_category_has_value = $gift_category_has_value;
        
        parent::indexAction();
    }    
    
}