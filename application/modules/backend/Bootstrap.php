<?php

class Backend_Bootstrap extends Zend_Application_Module_Bootstrap
{
    public function __construct($application)
    {
        parent::__construct($application);
        
        $configPath = APPLICATION_PATH . '/modules/backend/configs/config.ini';
        Zend_Registry::set('backend_config', new Zend_Config_Ini($configPath));
    }
    
    /**
     * Initialize navigation for backend module
     * 
     * @return void
     */
    // protected function _initNavigation()
    // {
        // $view = Zend_Layout::startMvc()->getView();
        // $config    = new Zend_Config_Ini(APPLICATION_PATH . '/modules/backend/configs/navigation.ini', 'nav');
        // $container = new Zend_Navigation($config);
//         
        // $view->navigation($container);
    // }
    
    /**
     * Initialize controller plugins 
     */
    protected function _initPlugins()
    {
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(
            new Qsoft_Controller_Plugin_Auth(new Qsoft_Acl(Zend_Registry::get('app_config')->acl->configPath))
        );
    }
}