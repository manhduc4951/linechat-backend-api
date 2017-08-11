<?php

/**
 * Dao_GroupInvite
 * 
 * @package Dao
 * @author duyld
 */
class Dao_GroupInvite extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable
{

    protected $_name = 'group_invite';
    
    protected $_rowClass = 'Dto_GroupInvite';
    
}
