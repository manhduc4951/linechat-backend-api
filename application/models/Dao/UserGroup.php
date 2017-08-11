<?php

/**
 * Dao_UserGroup
 * 
 * @package Dao
 * @author duyld
 */
class Dao_UserGroup extends Qsoft_Db_Table_Abstract
    implements Dao_Interface_EntityAccessable, Dao_Interface_PublicAccessable
{

    protected $_name = 'group';
    
    protected $_rowClass = 'Dto_UserGroup';
    
    /**
     * Retrieve all public instances
     * 
     * @param   Zend_Db_Select $select
     * @return  Qsoft_Db_Table_Rowset
     */
    public function fetchPublic(Zend_Db_Select $select = null)
    {
        return $this->fetchPublicBy();
    }
    
    /**
     * Retrieve a collection of public instance by provided name and value
     * 
     * @param   string          $name
     * @param   string|array    $value
     * @return  Qsoft_Db_Table_Rowset
     */
    public function fetchPublicBy($name = null, $value = null)
    {
        $select = $this->select()->where('is_public = ?', 1);
        if (null !== $name) {
            $select->where($name . ' = ?', $value);
        }
        
		return $this->fetchAll($select);
    }
    
    /**
     * {@inheritdoc}
     */
    public function fetchOnePublic($identity){}
    
    /**
     * {@inheritdoc}
     */
    public function fetchOnePublicBy($name, $value)
    {
        return $this->fetchOneBy($name, $value);
    }
    
    /**
     * Retrieve a public instance by provided name and value
     * 
     * @param   string  $name
     * @param   string  $value
     * @param   Zend_Db_Select  $select
     * @return  Qsoft_Dto_Abstract
     */
    public function fetchOneBy($name, $value, Zend_Db_Select $select = null)
    {
        $select = $this->select()
            ->from($this->_name)
            ->setIntegrityCheck(false)
            ->joinInner('user', 'user.id = group.user_id', array('unique_id'))
            ->where($this->_name . '.' . $name . ' = ?', $value)
        ;
        
        return $this->fetchRow($select);
    }
    
}
