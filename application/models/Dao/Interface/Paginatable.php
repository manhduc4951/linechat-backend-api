<?php

/**
 * Dao_Interface_Paginatable Interface
 * Interface for Dao that can provide pagination object
 * 
 * @package LCA450
 * @subpackage Dao_Interface
 * @author duyld
 */
interface Dao_Interface_Paginatable
{
    
    /**
     * Return list pagination
     * 
     * @param   int             $page
     * @param   int             $limit
     * @param   array|Zend_Db_Select  $select
     * @param   boolean         $fetchArray     Fetch the results item as array
     * @return  Zend_Paginator
     */
    public function getPagination($page = 1, $limit = null, $select = null, $fetchArray = false);
    
    /**
     * Generate filter query to display in the user list
     * 
     * @param   array 				$options
	 * @param	null|Zend_Db_Select	$select
     * @return  Zend_Db_Select
     */
    public function doFilter(array $criteria = array(), Zend_Db_Select $select = null);
    
}