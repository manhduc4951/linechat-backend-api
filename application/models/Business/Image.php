<?php

/**
 * Business_Image class
 * 
 * @package LineChatApp
 * @subpackage Business
 * @author duyld
 */
class Business_Image
{    
    /**
     * Image status Dao
     * 
     * @var Dao_ImageStatus
     */
    protected $imageStatusDao;
    
    /**
     * Constructor
     * 
     * @return Business_Lifelog
     */
    public function __construct()
    {        
        $this->imageStatusDao = new Dao_ImageStatus();
		
        return $this;
    }
    
    /**
     * Add new image status record for user profile image
     * 
     * @param Dto_User $userDto
     * @param Dto_UserImage $image
     * @return void
     */
    public function addUserProfileImage(Dto_User $userDto, Dto_UserImage $image)
    {
        $imageStatus = new Dto_ImageStatus();
        $imageStatus->user_id = $userDto->id;
        $imageStatus->user_img_id = $image->user_img_id;
        $imageStatus->created_at = $image->created_at;
        $imageStatus->type = Dto_ImageStatus::TYPE_USER;
        
        $this->imageStatusDao->insert($imageStatus);
    }
    
    /**
     * Add new image status record for user lifelog image
     * 
     * @param Dto_Lifelog $lifelogDto
     * @return void
     */
    public function addLifelogImage(Dto_Lifelog $lifelogDto)
    {
        $imageStatus = new Dto_ImageStatus();
        $imageStatus->user_id = $lifelogDto->user_id;
        $imageStatus->lifelog_id = $lifelogDto->id;
        $imageStatus->created_at = $lifelogDto->created_at;
        $imageStatus->type = Dto_ImageStatus::TYPE_LIFELOG;
        
        $this->imageStatusDao->insert($imageStatus);
    }
    
    /**
     * Add new image status record for user file transfer image
     * 
     * @param Dto_FileTransfer $fileDto
     * @return void
     */
    public function addFileTransferImage(Dto_FileTransfer $fileDto)
    {
        $imageStatus = new Dto_ImageStatus();
        $imageStatus->user_id = $fileDto->user_id;
        $imageStatus->file_transfer_id = $fileDto->id;
        $imageStatus->created_at = $fileDto->created_at;
        $imageStatus->type = Dto_ImageStatus::TYPE_FILE_TRANSFER;
        
        $this->imageStatusDao->insert($imageStatus);
    }
    
    public function blockImage($arrayUserImages)
    {
        $arrayUserId = array();
        $arrayLifelogId = array();
        $arrayFileTransferId = array();
        foreach($arrayUserImages as $key=>$userImage)
        {
            $tableId = explode('@',$userImage);
            $userImageTable = $tableId[0];
            $userImageId = $tableId[1];
            if($userImageTable == Dto_ImageStatus::TYPE_USER) {
                $arrayUserId[] = $userImageId;
            } elseif ($userImageTable == Dto_ImageStatus::TYPE_LIFELOG) {
                $arrayLifelogId[] = $userImageId;
            } elseif ($userImageTable == Dto_ImageStatus::TYPE_FILE_TRANSFER) {
                $arrayFileTransferId[] = $userImageId;
            }
        }
        $number = 0;
        if (!empty($arrayUserId)) {
            $userDao = new Dao_User();
            if ($userDao->dontDisplayImage($arrayUserId)) $number++;
        }
        if (!empty($arrayLifelogId)) {
            $lifelogDao = new Dao_Lifelog();
            if($lifelogDao->dontDisplayImage($arrayLifelogId)) $number++;
        }
        if (!empty($arrayFileTransferId)) {
            $fileTransferDao = new Dao_FileTransfer();
            if($fileTransferDao->dontDisplayImage($arrayFileTransferId)) $number++;
        }
        
        return array('number' => $number);
    }
}