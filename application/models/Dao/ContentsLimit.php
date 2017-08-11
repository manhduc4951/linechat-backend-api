<?php

/**
 * Dao_ContentsLimit
 * 
 * @package Dao
 * @author ducdm
 */
class Dao_ContentsLimit extends Qsoft_Db_Table_Abstract
    implements Dao_Interface_EntityAccessable
{
    
    protected $_name = 'contents_limit';
    
    protected $_rowClass = 'Dto_ContentsLimit';
    
}
