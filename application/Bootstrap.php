<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    /**
     * Application config
     */
    protected $_config;

    /**
     * Constructor
     *
     * @param  Zend_Application|Zend_Application_Bootstrap_Bootstrapper $application
     * @return void
     */
    public function __construct($application)
    {
        parent::__construct($application);
        
        // declare all error codes and the matched messages
        $errorMessages = include APPLICATION_PATH . '/configs/errorCode.php';
        Zend_Registry::set('error_messages', $errorMessages);

        // load application config
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        Zend_Registry::set('app_config', $this->_config);

        // load xmpp config
        Zend_Registry::set('xmpp_config', new Zend_Config_Ini(APPLICATION_PATH . '/configs/xmpp.ini'));

        $this->bootstrap('FrontController');
        $front = $this->getResource('FrontController');
        $request = new Zend_Controller_Request_Http();
        $front->setRequest($request);

        date_default_timezone_set($this->_config->site->timezone);
    }

    /**
     * Initialize autoload for Inclusion file 
     */
    protected function _initAutoload()
    {
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->setFallbackAutoloader(true);
    }

    /**
     * Initialize site mics 
     */
    protected function _initSite()
    {
        $this->bootstrap('view');
        $view = $this->getResource('view');

        $view->doctype('XHTML1_STRICT');
        $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html;charset=' . $this->_config->site->charset);
        $view->headTitle("BACKEND SYSTEM")->setSeparator(' ● ');
    }

    /**
     * Initialize View helpers 
     */
    protected function _initViewHelpers()
    {
        $this->bootstrap('view');
        $view = $this->getResource('view');

        Zend_Controller_Action_HelperBroker::addHelper(
            new Qsoft_Controller_Action_Helper_ViewRenderer($view)
        );

        $view->addHelperPath("ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper");
        $view->jQuery()->setLocalPath($view->baseUrl() . '/js/jqueryui/jquery-1.8.0.min.js')
            ->setUiLocalPath($view->baseUrl() . '/js/jqueryui/jquery-ui-1.8.23.custom.min.js')->enable();
        $view->addHelperPath("ZendX/View/Helper", "ZendX_View_Helper");
        $view->addHelperPath("Qsoft/View/Helper", "Qsoft_View_Helper");

        // Set default pagination template
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
    }

    /**
     * Initialize Controller action helpers 
     */
    protected function _initActionHelpers()
    {
        Zend_Controller_Action_HelperBroker::addPrefix('Qsoft_Helper');
    }

    /**
     * Initialize database connection
     */
    protected function _initDbConnection()
    {
        $this->bootstrap('multidb');
        $multidb = $this->getPluginResource('multidb');
        
        $mainAdapter = $multidb->getDb('main');
        $mainAdapter->setFetchMode(Zend_Db::FETCH_OBJ);
        Zend_Db_Table_Abstract::setDefaultAdapter($mainAdapter);
        Zend_Registry::set('mainAdapter', $mainAdapter);

        $chatAdapter = $multidb->getDb('chat');
        $chatAdapter->setFetchMode(Zend_Db::FETCH_OBJ);
        Zend_Registry::set('chatAdapter', $chatAdapter);
    }

    /**
     * Initialize Caches 
     */
    protected function _initCache()
    {
        if ($this->_config->site->cacheEnable) {
            $front = array(
                'lifetime' => $this->_config->site->cacheLifeTime,
                'automatic_serialization' => true
            );
            $back = array(
                'cache_dir' => $this->_config->site->cacheFolder
            );

            $cache = Zend_Cache::factory('Core', 'File', $front, $back);
            Zend_Registry::set('cache', $cache);
        }
    }

    /**
     * Initialize the route rule
     */
    protected function _initRoutes()
    {
        $frontController = Zend_Controller_Front::getInstance();
        $router = $frontController->getRouter();
    }

    /**
     * Initialize controller plugins 
     */
    protected function _initPlugin()
    {
        $frontController = Zend_Controller_Front::getInstance();
        $frontController->registerPlugin(
            new Qsoft_Controller_Plugin_Layout()
        );

        if ($this->_config->plugin->language->enable) {
            $frontController->registerPlugin(
                new Qsoft_Controller_Plugin_LanguageSwitcher($this->_config->plugin->language)
            );
        }
    }

    /**
     * Initialize logging
     */
    protected function _initLog()
    {
        if ($this->hasPluginResource("log")) {
            $resource = $this->getPluginResource("log");
            Zend_Registry::set('log', $resource->getLog());
            set_error_handler(array('Qsoft_ErrorHandle', 'handle'));
            register_shutdown_function(array('Qsoft_ErrorHandle', 'handleShutdown'));
        }
    }

}

