<?php

/**
 * Dao_AclPermission
 * 
 * @package Dao
 * @author duyld
 */
class Dao_AclPermission extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable
{
    
    protected $_name = 'acl_permission';
    
    protected $_rowClass = 'Dto_AclPermission';
    
    /**
     * Fetches all rows with details of controllers and actions.
     *
     * @return Qsoft_Db_Table_Rowset The row results per the Zend_Db_Adapter fetch mode.
     */
    public function fetchAllWithDetail()
    {
        $result = parent::fetchAll($this->select()
                ->setIntegrityCheck(false)
                ->from('acl_controller')
                ->joinLeft('acl_action', 'acl_action.controller_id = acl_controller.controller_id')
                ->joinLeft('acl_permission', 'acl_permission.action_id = acl_action.action_id')
                ->joinLeft('acl_role', 'acl_role.role_id = acl_permission.role_id')
                ->order('acl_controller.controller_id')
        );
    
        $data = array();
        foreach ($result as $value) {
            isset($data[$value['controller_name']]) OR $data[$value['controller_name']] = array();
    
            if ($value['action_name'] AND ! isset($data[$value['controller_name']][$value['action_name']])) {
                $data[$value['controller_name']][$value['action_name']] = array();
            }
    
            if ($value['permission'] !== null) {
                $data[$value['controller_name']][$value['action_name']][$value['role_name']] = array(
                        'id' => $value['permission_id'],
                        'value' => $value['permission'],
                );
            }
        }
        
        return $data;
    }
    
}