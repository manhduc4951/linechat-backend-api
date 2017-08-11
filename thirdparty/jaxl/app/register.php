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
	
    // Values passed to the constructor can also be defined as constants
    // List of constants can be found inside "../../env/jaxl.ini"
    // Note: Values passed to the constructor always overwrite defined constants
    $jaxl = new JAXL(array(
        'host'=> JAXL_HOST,
        'domain'=> JAXL_DOMAIN,
        'port' => JAXL_PORT,
        'authType'=> JAXL_AUTH_TYPE,
        'logLevel'=> JAXL_LOG_LEVEL,
        'logPath' => JAXL_LOG_PATH,
        'pidPath' => JAXL_PID_PATH
    ));
    
    $jaxl->requires('JAXL0077'); 
    
    function jaxl_register($payload, $jaxl) {
    	global $argv;
    	$jaxl->startStream(); 
    	$jaxl_register_data = array(
            'username' => $argv[1],
            'password' => $argv[2],
            'name'     => $argv[3],
        ); 
    	$jaxl->JAXL0077('register', '', JAXL_DOMAIN, 'jaxl_on_registered', $jaxl_register_data);
    }
    
    function jaxl_on_registered($payload, $jaxl) {
    	echo serialize($payload);
    	$jaxl->shutdown();
    }

    // Register callback on required hooks 
    $jaxl->addPlugin('jaxl_post_connect', 'jaxl_register');

    // Fire start Jaxl core
    $jaxl->startCore("stream");


