<?php

/**
 * Business_Acl class
*
* @package LineChatApp
* @subpackage Business
* @author duyld
*/
class Business_Acl
{
	/**
	 * Permission Dao
	 *
	 * @var Dao_AclPermission
	 */
	protected $permissionDao;
	
	/**
	 * Controller Dao
	 *
	 * @var Dao_AclController
	 */
	protected $controllerDao;
	
	/**
	 * Action Dao
	 *
	 * @var Dao_AclAction
	 */
	protected $actionDao;
	
	/**
	 * Role Dao
	 *
	 * @var Dao_AclRole
	 */
	protected $roleDao;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->permissionDao = new Dao_AclPermission();
		$this->controllerDao = new Dao_AclController();
		$this->actionDao = new Dao_AclAction();
		$this->roleDao = new Dao_AclRole();
	}
	
	/**
	 * Add new actions to acl module
	 *
	 * @param string $module
	 * @param string $controller
	 * @param string $action
	 * @return bool
	 */
	public function addPermission($module = 'default', $controller = 'index', $action = 'index')
	{
		if (empty($action) || empty($module) || empty($controller)) {
			return false;
		}
		
		// start transaction
		$this->roleDao->getAdapter()->beginTransaction();
		
		// make sure that the controller is exist
		$controllerDto = $this->controllerDao->fetchOneBy(array(
			'controller_name' => $controller,
			'module_name' => $module,
		));
		
		if ( ! $controllerDto) {
			$controllerDto = new Dto_AclController();
			$controllerDto->controller_name = $controller;
			$controllerDto->module_name = $module;
			
			$this->controllerDao->insert($controllerDto);
		}
		
		// create the action if not exist
		$actionDto = $this->actionDao->fetchOneBy(array(
				'controller_id' => $controllerDto->controller_id,
				'action_name' => $action,
		));
		
		$actionExist = true;
		if ( ! $actionDto) {
			$actionDto = new Dto_AclAction();
    		$actionDto->controller_id = $controllerDto->controller_id;
    		$actionDto->action_name = $action;
    		$this->actionDao->insert($actionDto);
    		
    		$actionExist = false;
		}
		
		// insert new action permissions for all roles
		$roles = $this->roleDao->fetchAll();
		$roles->remove('role_id', Dao_AclRole::ADMINISTRATOR_ROLE_ID);
		foreach ($roles as $role) {
		    // we need to check permission exist if the actions is exist before
		    if ($actionExist) {
		        $permissionExist = (boolean) $this->permissionDao->fetchOneBy(array(
	                'role_id' => $role->role_id,
	                'action_id' => $actionDto->action_id,
                ));
		    } else {
		        $permissionExist = false;
		    }
		    
		    if ( ! $permissionExist) {
		        $permissionDto = new Dto_AclPermission();
		        $permissionDto->role_id = $role->role_id;
		        $permissionDto->action_id = $actionDto->action_id;
		        $permissionDto->permission = Dto_AclPermission::PERMISSION_NOT_ALLOWED;
		        	
		        $this->permissionDao->insert($permissionDto);
		    }
		}
		
		$this->roleDao->getAdapter()->commit();
	
		return true;
	}
	
	/**
	 * Save privilege to database
	 *
	 * @param  array $permissions
	 * @return bool
	 */
	public function save($permissions)
	{
	    $this->permissionDao->getAdapter()->beginTransaction();
	    foreach ($permissions as $id => $permission) {
	        $this->permissionDao->updateAsArray(
	                array('permission' => (empty($permission) ? '0' : '1')),
	                array('permission_id = ?' => $id)
	        );
	    }
	    
	    $this->permissionDao->getAdapter()->commit();
	    
	    // remove the config file to do update the changes
	    $this->releaseAclConfig();
	
	    return true;
	}
	
	/**
	 * Remove the config file to do update the changes
	 * @return void
	 */
	public function releaseAclConfig()
	{
	    $configPath = Zend_Registry::get('app_config')->acl->configPath;
	    is_file($configPath) AND unlink($configPath);
	}
	
}