<?php

    /**
     * Sample command line bot for sending a message
     * Usage: cd /path/to/jaxl
     * 	      Edit username/password below
     * 		  Run from command line: /path/to/php sendMessage.php "username@gmail.com" "Your message"
     * 		  View jaxl.log for detail
     * 
     * Read More: http://jaxl.net/examples/sendMessage.php
    */

	// Initialize Jaxl Library
    require_once dirname(__FILE__) . '/../core/jaxl.class.php';
    
    // Load the constants config
    require_once dirname(__FILE__) . '/../config/constants.php';
    
    global $argv;
	
    // Values passed to the constructor can also be defined as constants
    // List of constants can be found inside "../../env/jaxl.ini"
    // Note: Values passed to the constructor always overwrite defined constants
    $jaxl = new JAXL(array(
	    'user' => $argv[1],
        'pass' => $argv[2],
        'host'=> JAXL_HOST,
        'boshHost'=> JAXL_HOST,
        'domain'=> JAXL_DOMAIN,
        'port' => JAXL_PORT,
        'authType'=> JAXL_AUTH_TYPE,
        'logLevel'=> JAXL_LOG_LEVEL,
        'logPath' => JAXL_LOG_PATH,
        'pidPath' => JAXL_PID_PATH,
    ));
    
    // Include required XEP's
    $jaxl->requires('JAXL0060');
    $jaxl->requires('JAXL0016');
    $jaxl->requires('JAXL0199');
    
    return $jaxl;
