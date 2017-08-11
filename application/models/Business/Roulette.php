<?php

/**
 * Business_Roulette
 * 
 * @package LineChatApp
 * @subpackage Business
 * @author ducdm
 */
class Business_Roulette
{
    /**
     * Roulette Dao
     * 
     * @var Dao_Roulette
     */
    protected $rouletteDao;
    
    /**
     * Constructor
     * 
     * @return Business_Roulette
     */
    public function __construct()
    {        
        $this->rouletteDao = new Dao_Roulette();
		
        return $this;
    }
    
    public function updateRoulette($rouletteData)
    {
        foreach($rouletteData as $key => $roulette)
            {
                $rouletteDto = new Dto_Roulette(array('data'=>$roulette));
                $rouletteDto->roulette_id = $key;
                $rouletteDto->updated_at = Qsoft_Helper_Datetime::currentTime();
                unset($rouletteDto->created_at);
                
                if($rouletteDto->type == Dto_Roulette::ROULETTE_TYPE_ITEM) {
                    $rouletteDto->gift_id = null;
                    $rouletteDto->point = null;
                } elseif($rouletteDto->type == Dto_Roulette::ROULETTE_TYPE_GIFT) {
                    $rouletteDto->item_id = null;
                    $rouletteDto->point = null;
                } elseif($rouletteDto->type == Dto_Roulette::ROULETTE_TYPE_POINT)
                {
                    $rouletteDto->item_id = null;
                    $rouletteDto->gift_id = null;
                } else {
                    $rouletteDto->item_id = null;
                    $rouletteDto->gift_id = null;
                    $rouletteDto->point = null;
                }
                
                $this->rouletteDao->update($rouletteDto);  
            }
    }    
}