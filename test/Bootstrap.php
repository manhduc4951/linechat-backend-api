<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

require_once './vendor/autoload.php';

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));


// Define path to library directory
defined('LIBRARY_PATH')
    || define('LIBRARY_PATH', realpath(dirname(__FILE__) . '/../library'));

// Define path to thirdparty directory
defined('THIRDPARTY_PATH')
    || define('THIRDPARTY_PATH', realpath(dirname(__FILE__) . '/../thirdparty'));

// Define path to caches directory
defined('CACHE_PATH')
    || define('CACHE_PATH', realpath(dirname(__FILE__) . '/../data/caches'));

// Define path to logs directory
defined('LOG_PATH')
    || define('LOG_PATH', realpath(dirname(__FILE__) . '/../data/logs'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));
	

// Set include path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    realpath(APPLICATION_PATH . '/library'),
    realpath(APPLICATION_PATH . '/models'),
    realpath(APPLICATION_PATH . '/forms'),
    realpath(APPLICATION_PATH . '/helpers'),
    get_include_path(),
)));

initAutoload();

// declare all error codes and the matched messages
$errorMessages = include APPLICATION_PATH . '/configs/errorCode.php';
Zend_Registry::set('error_messages', $errorMessages);

// load application config
$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
Zend_Registry::set('app_config', $config);

// load xmpp config
Zend_Registry::set('xmpp_config', new Zend_Config_Ini(APPLICATION_PATH . '/configs/xmpp.ini'));

date_default_timezone_set($config->site->timezone);
initDbConnection();
//initLog();


/**
 * Initialize autoload for Inclusion file 
 */
function initAutoload()
{
	require_once './library/Zend/Loader/Autoloader.php';
    $autoloader = Zend_Loader_Autoloader::getInstance();
    $autoloader->setFallbackAutoloader(true);
}

/**
 * Initialize database connection
 */
function initDbConnection()
{
	// main adapter
	$mainAdapter = Zend_Db::factory('Pdo_Mysql', array(
	    'host'     => '122.255.76.176',
	    'username' => 'root',
	    'password' => 'WaQI[Ph6',
	    'dbname'   => 'openfire',
	    'charset'  => 'utf8'
	));
	
	$mainAdapter->setFetchMode(Zend_Db::FETCH_OBJ);
    Zend_Db_Table_Abstract::setDefaultAdapter($mainAdapter);
    Zend_Registry::set('mainAdapter', $mainAdapter);
	
	// xmpp chat server adapter
	$chatAdapter = Zend_Db::factory('Pdo_Mysql', array(
	    'host'     => '122.255.76.176',
	    'username' => 'root',
	    'password' => 'WaQI[Ph6',
	    'dbname'   => 'openfire',
	    'charset'  => 'utf8'
	));
	
	$chatAdapter->setFetchMode(Zend_Db::FETCH_OBJ);
    Zend_Registry::set('chatAdapter', $chatAdapter);
}

function initLog()
{
	$writer = new Zend_Log_Writer_Stream(Zend_Registry::get('app_config')->resources->log->stream->writerParams->stream);
	$logger = new Zend_Log($writer);
	
	Zend_Registry::set('log', $logger);
    set_error_handler(array('Qsoft_ErrorHandle', 'handle'));
    register_shutdown_function(array('Qsoft_ErrorHandle', 'handleShutdown'));
}
