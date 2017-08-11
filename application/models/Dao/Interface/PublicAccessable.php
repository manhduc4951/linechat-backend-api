<?php

/**
 * Dao_Interface_PublicAccessable Interface
 * Interface for Dao that accessable by public api
 * 
 * @package LCA450
 * @subpackage Dao_Interface
 * @author duyld
 */
interface Dao_Interface_PublicAccessable
{
    /**
     * Retrieve a public instance by provided identity
     * 
     * @param   string  $identity
     * @return  Qsoft_Dto_Abstract
     */
    public function fetchOnePublic($identity);
    
    /**
     * Retrieve a public instance by provided name and value
     * 
     * @param   string  $name
     * @param   string  $value
     * @return  Qsoft_Dto_Abstract
     */
    public function fetchOnePublicBy($name, $value);
    
    /**
     * Retrieve all public instances
     * 
     * @param   Zend_Db_Select $select
     * @return  Qsoft_Db_Table_Rowset
     */
    public function fetchPublic(Zend_Db_Select $select = null);
    
    /**
     * Retrieve a collection of public instance by provided name and value
     * 
     * @param   string          $name
     * @param   string|array    $value
     * @return  Qsoft_Db_Table_Rowset
     */
    public function fetchPublicBy($name = null, $value = null);
    
}