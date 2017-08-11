<?php

/**
 * Core_CrudController
 * 
 * @package LineChatApp
 * @subpackage Controller
 * @author duyld
 */
class Core_CrudController extends Qsoft_Controller_Action
{
	protected $_businessClass = 'Business_Crud';
	
	/**
     * Get the business model
     * 
     * @return Business_Crud
     */
    protected function getBusiness()
    {
        if ( ! $this->_business) {
            $this->_business = new $this->_businessClass($this->view);
        }
        
        return $this->_business;
    }
	
	/**
	 * Index page, show generate form
	 */
	public function indexAction()
	{
		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			
			if ( ! empty($data['controller']['controller_name'])) {
				$this->_processCreateController($data['controller']);
			}
			
			if ( ! empty($data['business']['business_name'])) {
				
			}
			
			if ( ! empty($data['dao']['dao_name'])) {
				
			}
		}
		
		$this->view->title = 'Crud controller';
		$this->view->moduleNames = $this->getHelper('application')->getModuleNames();
	}
	
	/**
	 * Generate controller action
	 * 
	 * @param	array 	$data
	 * @return	boolean
	 */
	protected function _processCreateController($data)
	{
		// retrieve and validate parameters
		if (strlen($data['controller_name']) < 2) {
			$this->warningMessage('The controller name must be more than 1 characters in length');
			return false;
		}
		
		// let business perform this action
		return $this->getBusiness()->generateController($data['controller_name'], $data['module_name'], $data);
	}
	
	/**
	 * Generate business action
	 * 
	 * @param	array 	$data
	 * @return	boolean
	 */
	protected function _processCreateBusiness($data)
	{
		// retrieve and validate parameters
		if (strlen($data['business_name']) < 2) {
			$this->warningMessage('The business name must be more than 1 characters in length');
			return false;
		}
		
		// let business perform this action
		return $this->getBusiness()->generateController($data['controller_name'], $data['module_name'], $data);
	}
	
}
