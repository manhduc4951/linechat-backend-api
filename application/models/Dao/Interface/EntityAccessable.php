<?php

/**
 * Dao_Interface_EntityAccessable Interface
 * Interface for Dao that can retrieve entities
 * 
 * @package LCA450
 * @subpackage Dao_Interface
 * @author duyld
 */
interface Dao_Interface_EntityAccessable
{
    /**
     * Retrieve a public instance by provided identity
     * 
     * @param   string  $identity
     * @return  Qsoft_Dto_Abstract
     */
    public function fetchOne($identity);
    
    /**
     * Retrieve a public instance by provided name and value
     * 
     * @param   string  $name
     * @param   string  $value
     * @param   Zend_Db_Select  $select
     * @return  Qsoft_Dto_Abstract
     */
    public function fetchOneBy($name, $value, Zend_Db_Select $select = null);
    
    /**
     * Retrieve a collection of public instance by provided name and value
     * 
     * @param   string|array    $name
     * @param   string|array    $value
     * @param   Zend_Db_Select  $select
     * @return  Qsoft_Db_Table_Rowset
     */
    public function fetchAllBy($name = null, $value = null, Zend_Db_Select $select = null);
    
}