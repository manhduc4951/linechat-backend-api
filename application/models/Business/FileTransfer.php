<?php

/**
 * Business_FileTransfer class
 * 
 * @package LineChatApp
 * @subpackage Business
 * @author duyld
 */
class Business_FileTransfer
{
    /**
     * File transfer DAO
     * 
     * @var Dao_FileTransfer
     */
    protected $fileDao;
    
    /**
     * File transfer config
     * 
     * @var Zend_Config
     */
    protected $fileConfig;
    
    /**
     * Constructor
     * 
     * @return Business_User
     */
    public function __construct()
    {
        $this->fileDao = new Dao_FileTransfer();
        $this->fileConfig = Zend_Registry::get('app_config')->user->file;
        
        return $this;
    }
    
    /**
     * Initialize a file transfer adapter
     * 
     * @return  Zend_File_Transfer
     */
    public function getFileTransferAdapter()
    {
        $adapter = new Zend_File_Transfer();
        $adapter
            ->addValidator('FilesSize', false, array('max' => $this->fileConfig->limitSize, 'bytestring' => true))
            ->addValidator('Extension', false, $this->fileConfig->types)
            ->addFilter(new Qsoft_Filter_File_UniqueName($this->fileConfig->uploadPath))
            ->addFilter(new Qsoft_Filter_File_ImageThumbnail(array(
                'width'     => $this->fileConfig->thumbnail->width,
                'height'    => $this->fileConfig->thumbnail->height,
                'target'    => $this->fileConfig->thumbnail->uploadPath,
            )))
        ;
        
        return $adapter;
    }
    
    /**
     * Create new file transfer instance
     * 
     * @param   Dto_FileTransfer    $fileDto
     * @param   Zend_File_Transfer  $fileAdapter
     * @param   Dto_User            $userDto
     * @return  array   Result array
     */
    public function create(Dto_FileTransfer $fileDto, Zend_File_Transfer $fileAdapter, Dto_User $userDto)
    {
        $this->fileDao->getAdapter()->beginTransaction();
        
        $fileDto->file_name = $fileAdapter->getFileName(null, false);
        $fileDto->user_id = $userDto->id;
        
        // insert file transfer record
        $this->generateToken($fileDto);
        $this->fileDao->insert($fileDto);
        
        // update reference in image status table
        if ($fileDto->hasThumbnail()) {
            $imageBusiness = new Business_Image();
            $imageBusiness->addFileTransferImage($fileDto);
        }
        
        $this->fileDao->getAdapter()->commit();
        return array('status' => true);
    }
    
    /**
     * Delete an file transfer instance
     * 
     * @param   Dto_FileTransfer    $fileDto
     * @return  void
     */
    public function delete(Dto_FileTransfer $fileDto)
    {
        @unlink($fileDto->getFilePath());
        $this->fileDao->delete($fileDto);
    }
    
    /**
     * Generate an unique token to accessable file transfer
     * 
     * @param   Dto_FileTransfer    $fileDto
     * @return  Business_FileTransfer
     */
    public function generateToken(Dto_FileTransfer $fileDto)
    {
        $fileDto->token = Qsoft_Helper_String::random('Alnum', 16);
        
        return $this;
    }

}
