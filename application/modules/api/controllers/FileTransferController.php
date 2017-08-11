<?php

class Api_FileTransferController extends Qsoft_Rest_Controller
{
    
    protected $_businessClass = 'Business_FileTransfer';
    
    protected $_daoClass = 'Dao_FileTransfer';
    
    /**
     * Get the business model
     * 
     * @return Business_FileTransfer
     */
    protected function getBusiness()
    {
        return parent::getBusiness();
    }
    
    /**
     * Upload action
     */
    public function postAction()
    {
        // initialize a file transfer adapter
        $adapter = $this->getBusiness()->getFileTransferAdapter();
        
        // validate and receive the upload file
        if ( ! $adapter->isUploaded("file")) {
            $this->failure(ERROR_UPLOAD_FILE_INVALID);
        }
        
        if ( ! $adapter->isValid("file")) {
            Zend_Registry::get('log')->log("file transfer failure: ". print_r($adapter->getErrors(), true), Zend_Log::ERR);
            if (isset($_FILES)) {
                Zend_Registry::get('log')->log("file transfer failure \$_FILES: ". print_r($_FILES, true), Zend_Log::ERR);
            }
            
            $this->failure(ERROR_UPLOAD_FILE_INVALID);
        }
        
        try {
            $adapter->receive("file");
        } catch (Zend_File_Transfer_Exception $e) {
            $this->failure(ERROR_CANNOT_RECEIVE_FILE);
        }
        
        // create new file transfer record
        $apiUser = Zend_Registry::get('api_user');
        $fileDto = new Dto_FileTransfer;
        $this->getBusiness()->create($fileDto, $adapter, $apiUser);
        
        $this->success(array(
            'download_link' => $this->getHelper('url')->generate(
                array('id' => $fileDto->id),
                true
            ),
            'download_token' => $fileDto->token,
            'thumbnail' => $fileDto->getThumbnailUrl(),
        ));
    }
    
    /**
     * Download action
     */
    public function getAction()
    {
        // try to find out the request file
        $id = trim($this->_getParam('id', ''));
        $token = trim($this->_getParam('download_token', ''));
        $fileDto = $this->getDao()->fetchOne($id);
        
        if ( ! $fileDto OR $fileDto->isBlocked()) {
            $this->failure(ERROR_UPLOAD_FILE_NOT_FOUND);
        }
        
        if ( ! is_readable($fileDto->getFilePath())) {
            $this->getBusiness()->delete($fileDto);
            $this->failure(ERROR_UPLOAD_FILE_NOT_FOUND);
        }
        
        // this download is allowed if the provided token is matched with file token
        if ($token != $fileDto->token) {
            $this->failure(ERROR_AUTHORIZE_DENY);
        }
        
        return $this->download($fileDto->getFilePath());
    }
    
}