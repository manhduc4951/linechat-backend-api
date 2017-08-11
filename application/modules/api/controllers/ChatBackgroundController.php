<?php

class Api_ChatBackgroundController extends App_Rest_Controller
{
    
    protected $_daoClass = 'Dao_ChatBackground';
	
	/**
     * Get the Dao object
     * 
     * @return Dao_ChatBackground
     */
	public function getDao()
	{
		return parent::getDao();
	}
    
    /**
     * Retrieve all backgrounds action
     */
    public function indexAction()
    {
        $this->success(array('backgrounds' => $this->getDao()->fetchAll()->toEndUserArray()));
    }
}
