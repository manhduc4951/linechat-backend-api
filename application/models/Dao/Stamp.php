<?php

/**
 * Dao_Stamp
 * 
 * @package Dao
 * @author ducdm
 */
class Dao_Stamp extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable, Dao_Interface_PublicAccessable
{

    protected $_name = 'stamp';
    
    protected $_rowClass = 'Dto_Stamp';
    
    /**
     * Fetches all rows with purchase status
     *
     * @param integer                           $userId
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @param int                               $count  OPTIONAL An SQL LIMIT count.
     * @param int                               $offset OPTIONAL An SQL LIMIT offset.
     * @return Zend_Db_Table_Rowset_Abstract The row results per the Zend_Db_Adapter fetch mode.
     */
    public function fetchAllPublicWithPurchaseStatus($userId, $order = null, $count = null, $offset = null)
    {
        $select = $this->select()
            ->setIntegrityCheck(false)
            ->from('stamp')
            ->joinLeft(
                'user_stamp',
                'user_stamp.stamp_id = stamp.stamp_id AND user_stamp.user_id = :user_id',
                'IF(user_stamp.user_stamp_id,1,0) AS purchased'
                )
            ->limit($count, $offset)
            ->bind(array(
                'user_id' => $userId
            ))
        ;
        
        if ($order) {
            $select->order($order);
        }
        
        return $this->fetchPublic($select);
    }
    
    /**
     * Retrieve all public instances
     * 
     * @param   Zend_Db_Select $select
     * @return  Qsoft_Db_Table_Rowset
     */
    public function fetchPublic(Zend_Db_Select $select = null)
    {
        if ( ! $select instanceof Zend_Db_Select) {
            $select = $this->select();
        }
        
        $this->addPublicQuery($select);
        
        return $this->fetchAll($select);
    }
    
    /**
     * Retrieve a public instance by provided identity
     * 
     * @param   string  $identity
     * @return  Dto_Stamp
     */
    public function fetchOnePublic($identity)
    {
        return $this->fetchOnePublicBy('stamp_id', $identity);
    }
    
    /**
     * Retrieve a public instance by provided name and value
     * 
     * @param   string  $name
     * @param   string  $value
     * @return  Dto_Stamp
     */
    public function fetchOnePublicBy($name, $value)
    {
        $select = $this->select();
        $this->addPublicQuery($select);
        $this->addMixedWhere($select, $name, $value);
        
        return $this->fetchRow($select);
    }
    
    /**
     * Retrieve a collection of public instance by provided name and value
     * 
     * @param   string          $name
     * @param   string|array    $value
     * @return  Qsoft_Db_Table_Rowset
     */
    public function fetchPublicBy($name = null, $value = null){}
    
    /**
     * Add public where query to a query
     * 
     * @param Zend_Db_Select $select
     * @return Dao_Stamp
     */
    protected function addPublicQuery(Zend_Db_Select $select)
    {
        $select->where('stamp.show_hide = 1');
    }
    
}
