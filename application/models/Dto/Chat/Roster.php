<?php

/**
 * Dto_Chat_Roster
 * 
 * @package Dto_Chat
 * @author ducdm
 */
class Dto_Chat_Roster extends Qsoft_Dto_Abstract
{

    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_Chat_Roster';
    
    const SUBSCRIPTION_STATE_BOTH = 3;
    const SUBSCRIPTION_STATE_FROM = 2;
    const SUBSCRIPTION_STATE_TO = 1;
    const SUBSCRIPTION_STATE_NONE = 0;
    
}
