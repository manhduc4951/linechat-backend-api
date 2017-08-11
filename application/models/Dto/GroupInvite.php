<?php

/**
 * Dto_GroupInvite
 * 
 * @package Dto
 * @author ducdm
 */
class Dto_GroupInvite extends Qsoft_Dto_Abstract
{
    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_GroupInvite';
	
	/**
	 * Convert to array that contains user info only
	 * 
	 * @return array
	 */
	public function toUserArray()
	{
		return $this->toArray(array('unique_id' => 'id'));
	}
    
}