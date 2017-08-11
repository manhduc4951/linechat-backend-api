<?php

/**
 * Business_Crud class
 * 
 * @package LineChatApp
 * @subpackage Core_Business
 * @author duyld
 */
class Business_Crud
{
	/**
	 * Backup file suffix characters
	 * @var string
	 */
	const FILE_BACKUP_SUFFIX = '~';
	
	/**
	 * Template name of controller
	 * @var	string
	 */
	const CONTROLLER = 'controller';
	
	/**
	 * Template name of business
	 * @var	string
	 */
	const BUSINESS = 'business';
	
	/**
	 * Template name of dao
	 * @var	string
	 */
	const DAO = 'dao';
	
	/**
	 * Controller type RESTful
	 * @var	string
	 */
	const CONTROLLER_TYPE_REST = 'rest';
	
	/**
	 * Controller type backend
	 * @var	string
	 */
	const CONTROLLER_TYPE_BACKEND = 'backend';
	
	/**
	 * Zend view of current action
	 * @param	Zend_View
	 */
	protected $view;
	
	/**
	 * Constructor
	 * 
	 * @param	Zend_View	$view
	 * @return 	Business_Crud
	 */
	public function __construct(Zend_View $view)
	{
		$this->view = $view;
		
		return $this;
	}
	
	/**
	 * Generate a controller
	 * 
	 * @param	string	$controllerName
	 * @param	string	$module
	 * @param	array 	$params			The array contains business class or dao class (optional)
	 * @return	boolean
	 */
	public function generateController($controllerName, $moduleName = 'default', $params = array())
	{
		// generate controller full path
		$controllerFullPath = $this->getControllerFullPath($controllerName, $moduleName);
		
		// if this controller is existing, make a backup
		if (is_readable($controllerFullPath)) {
			copy($controllerFullPath, $controllerFullPath . self::FILE_BACKUP_SUFFIX);
		}
		
		// prepare parameters
		$params['controllerParent'] = $this->getControllerParent($params['controller_type']);
		
		// render file
		$data = $this->render(self::CONTROLLER, array_merge($params, compact('controllerName', 'moduleName')));
		return file_put_contents($controllerFullPath, $data);
	}
	
	/**
	 * Render a code generate template
	 * 
	 * @param	string	$template	The template name, ie. controller, business
	 * @param	array 	$params		Parameters to send to template
	 * @return	string
	 */
	public function render($template, $params = array())
	{
	    $this->view->assign($params);
	    return $this->view->render($this->getTemplateFullPath($template));
	}
	
	/**
	 * Return the full path of given controller name and module
	 * 
	 * @param	string	$controllerName
	 * @param	string	$module
	 * @return	string
	 */
	public function getControllerFullPath($controllerName, $moduleName = 'default')
	{
		$controllerFullPath = 'controllers' . DIRECTORY_SEPARATOR . ucfirst($controllerName) . 'Controller.php';
		switch ($moduleName) {
			case 'default':
				$controllerFullPath = APPLICATION_PATH . DIRECTORY_SEPARATOR . $controllerFullPath;
				break;
			
			default:
				$controllerFullPath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' .
					DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . $controllerFullPath;
				break;
		}
		
		return $controllerFullPath;
	}
	
	/**
	 * Return the parent class of controller
	 * 
	 * @param	string	$type
	 * @return	string
	 */
	public function getControllerParent($type)
	{
		switch ($type) {
			case self::CONTROLLER_TYPE_REST:
				return 'Qsoft_Rest_Controller';
				break;
			
			case self::CONTROLLER_TYPE_BACKEND:
				return 'Qsoft_Controller_Action';
				break;
			
			default:
				return 'Qsoft_Controller_Action';
				break;
		}
	}
	
	/**
	 * Return the full path of template file that use to generate code
	 * 
	 * @param	string	$name
	 * @return	string
	 */
	public function getTemplateFullPath($name)
	{
		return 'crud' . DIRECTORY_SEPARATOR . 'generate' . DIRECTORY_SEPARATOR . $name . '.phtml';
	}
	
}