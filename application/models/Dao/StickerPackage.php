<?php

/**
 * Dao_StickerPackage
 * 
 * @package Dao
 * @author sonvq
 */
class Dao_StickerPackage extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable
{

    protected $_name = 'sticker_package';
    
    protected $_rowClass = 'Dto_StickerPackage';

}
