<?php

/**
 * Dao_Emoticon
 * 
 * @package Dao
 * @author ducdm
 */
class Dao_UserEmoticon extends Qsoft_Db_Table_Abstract
    implements Dao_Interface_EntityAccessable
{
    
    protected $_name = 'feeling_img';
    
    protected $_rowClass = 'Dto_UserEmoticon';
    
}
