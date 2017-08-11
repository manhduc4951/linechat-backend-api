<?php
/**
 * Permission Controller
 *
 * @package Controller
 */
class PermissionController extends Qsoft_Controller_Backend_Action
{
	protected $_businessClass = 'Business_Acl';
	
    protected $_daoClass = 'Dao_AclPermission';
    
    /**
     * Roles DAO
     * 
     * @var Dao_AclRole
     */
    protected $roleDao;
    
    /**
     * Initialize object
     *
     * @return void
     */
    public function init()
    {
    	parent::init();
    	$this->roleDao = new Dao_AclRole();
    }
    
    /**
     * Get the business model
     *
     * @return Business_Acl
     */
    protected function getBusiness()
    {
    	return parent::getBusiness();
    }
    
    /**
     * Get the dbtable
     *
     * @return Dao_AclPermission
     */
    protected function getDao()
    {
    	return parent::getDao();
    }
    
    /**
     * Default action for listing
     */
    public function indexAction()
    {//echo '<pre>'; print_r($_POST['update']); echo '</pre>'; die;
        if ($this->_request->isPost()) {
            
            if (isset($_POST['update'])) {
                $data = $this->_getParam('permission');
                //echo '<pre>'; print_r($data); echo '</pre>'; die;
                $this->getBusiness()->save($data);
            }
            
            $this->flashNoticeMessage('Successfully.');
            $this->_redirect($this->_helper->Url->generate(array('action' => 'index')));
        } //die('here');
        
        $this->view->title = $this->view->translate('Permission list');
        $this->view->roles = $this->roleDao->fetchAll();
        $this->view->permissions = $this->getDao()->fetchAllWithDetail();
    }
    
    /**
     * Scan action
     */
    public function scanAction()
    {
        ini_set('execute_time', 0);
        // get scanned actions from application
        $scanned = $this->_helper->actions->scanAllActions(
            'backend',
            array('error', 'auth', 'permission'),
            array('access-denied')
        );
        
        foreach ($scanned as $module => $controllers) {
            foreach ($controllers as $controller => $actions) {
                foreach ($actions as $action) {
                	$this->getBusiness()->addPermission($module, $controller, $action);
                }
            }
        }
        
        $this->getBusiness()->releaseAclConfig();
        
        $this->flashNoticeMessage('Successfully.');
        $this->_redirect(Qsoft_Helper_Url::generate(array('action' => 'index')));
    }
}
