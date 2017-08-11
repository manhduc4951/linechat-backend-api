<?php

class Api_StampController extends Qsoft_Rest_Controller
{

    protected $_daoClass = 'Dao_Stamp';
    
    /**
     * Stamp item dao
     * 
     * @var Dao_StampItem
     */
    protected $stampItemDao;
    
    /**
     * (non-PHPdoc)
     * @see Qsoft_Rest_Controller::init()
     */
    public function init()
    {
        $this->stampItemDao = new Dao_StampItem();
        return parent::init();
    }
    
    /**
     * Get the Dao
     *
     * @return Dao_Stamp
     */
    protected function getDao()
    {
        return parent::getDao();
    }

    /**
     * Search list of stamps
     */
    public function indexAction()
    {
        $userDto = Zend_Registry::get('api_user');
        $stamps = $this->getDao()->fetchAllPublicWithPurchaseStatus($userDto->id);
        
        $this->success(array('stamps' => $stamps->toEndUserArray()));
    }
    
    /**
     * Get stamp detail
     */
    public function getAction()
    {
        $stampDto = $this->getDao()->fetchOnePublic($this->_getParam('id', '-1'));
        if ( ! $stampDto) {
            return $this->notFoundAction();
        }
        
        $stampDto = $stampDto->toEndUserArray();
        $stampDto['items'] = $this->stampItemDao
            ->fetchAllBy('stamp_id', $stampDto['stamp_id'])
            ->toEndUserArray()
        ;

        $this->success(array('stamps' => $stampDto));
    }
    
    /**
     * Download zip file or stamp item action
     */
    public function downloadAction()
    {
        $path = $this->getRequest()->getPathInfo();
        $path = substr($path, strpos($path, 'download') + 9);
        $path = Zend_Registry::get('app_config')->stamp->zip->uploadPath . $path;
        
        if ( ! is_file($path)) {
            return $this->notFoundAction();
        }
        
        $this->download($path);
    }

}
