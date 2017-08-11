<?php

/**
 * Core_Bootstrap
 * 
 * @package LineChatApp
 * @subpackage Core Module Bootstrap
 * @author duyld
 */
class Core_Bootstrap extends Zend_Application_Module_Bootstrap
{

    /**
     * Constructor
     *
     * @param  Zend_Application|Zend_Application_Bootstrap_Bootstrapper $application
     * @return void
     */
    public function __construct($application)
    {
        parent::__construct($application);
		
		// Set include path for models
		set_include_path(implode(PATH_SEPARATOR, array(
		    realpath(APPLICATION_PATH . '/modules/core/models'),
		    get_include_path(),
		)));
    }
    
}