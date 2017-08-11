<?php

class AuthController extends Qsoft_Controller_Backend_Action
{
	
	protected $_daoClass = 'Dao_AdminUser';
	
	/**
	 * Get the dbtable
	 *
	 * @return Dao_AdminUser
	 */
	protected function getDao()
	{
		return parent::getDao();
	}
	
	/**
	 * Initialize object
	 *
	 * Called from {@link __construct()} as final step of object instantiation.
	 *
	 * @return void
	 */
	public function init()
	{
		parent::init();
		$this->_helper->layout()->setLayout("auth");
	}
    
    /**
     * Login action
     */
    public function indexAction()
    {
        $form = new Form_Login;
        
        if ($this->_request->isPost()) {
            
            if ($form->isValid($this->_request->getPost())) {
                if ($this->_process($form->getValues())) {
                    // we're authenticated! Redirect to the home page
                    $this->flashNoticeMessage("Successful login");
                    
                    $currentUri = $this->_request->getPathInfo();
                    if (strpos($currentUri, 'auth') === false) {
                        $this->redirect($currentUri);
                    } else {
                        $this->_helper->redirector('index', 'user');
                    }
                } else {
                    $this->warningMessage("Your username or password is wrong");
                }
            }
        }
        
        $this->view->title = $this->view->translate("Login");
        $this->view->form = $form;
    }
    
    /**
     * Authenticate processing
     * 
     * @param  array $values
     * @return bool True if login successfully otherwise return False
     */
    protected function _process($values)
    {
        // Get our authentication adapter and check credentials
        $adapter = $this->_getAuthAdapter();
        $adapter->setIdentity($values['user_name']); 
        $adapter->setCredential($values['password']);

        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate($adapter);
        
        if ($result->isValid()) {
            $user = $adapter->getResultRowObject();
            // create log            
            $adminLoginLogDto = new Dto_AdminLoginLog();
            $adminLoginLogDto->admin_user_id = $user->admin_user_id;
            $adminLoginLogDto->ip_address = $this->getRequest()->getClientIp();
            $adminLoginLogDto->login_pc_name = gethostname();
            $adminLoginLogDto->content = 'Login successful';
            // insert to db
            $adminLoginLogDao = new Dao_AdminLoginLog();
            $adminLoginLogDao->insert($adminLoginLogDto);
            
            $roleDao = new Dao_AclRole();
            $roleDto = $roleDao->fetchOne($user->admin_role_id);
            $user->admin_role_name = $roleDto->role_name;
            $auth->getStorage()->write($user);
            
            // also update the last access of user
            $userDto = new Dto_AdminUser(array('data' => get_object_vars($user)));
            $userDto->last_access = Qsoft_Helper_Datetime::current();
            $this->getDao()->update($userDto, 'last_access');
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Get authenticae adapter
     * 
     * @return Qsoft_Auth_Adapter_DbTable 
     */
    protected function _getAuthAdapter() 
    {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);

        $authAdapter->setTableName('admin_user')
            ->setIdentityColumn('login_id')
            ->setCredentialColumn('password')
            ->setCredentialTreatment('MD5(?)');

        return $authAdapter;
    }
    
    /**
     * Logout action
     */
    public function logoutAction()
    {
        $adminLoginLogDto = new Dto_AdminLoginLog();
        $adminLoginLogDto->admin_user_id = Zend_Auth::getInstance()->getIdentity()->admin_user_id;
        $adminLoginLogDto->ip_address = $this->getRequest()->getClientIp();
        $adminLoginLogDto->login_pc_name = gethostname();
        $adminLoginLogDto->content = 'Logout';
        // insert to db
        $adminLoginLogDao = new Dao_AdminLoginLog();
        $adminLoginLogDao->insert($adminLoginLogDto);
        
        Zend_Auth::getInstance()->clearIdentity();
        $this->_helper->redirector('index', 'auth', 'backend'); // back to login page
    }
    
}