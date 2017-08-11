<?php

/**
 * Business_Lifelog class
 * 
 * @package LineChatApp
 * @subpackage Business
 * @author ducdm
 */
class Business_Lifelog
{    
    /**
     * Lifelog DAO
     * 
     * @var Dao_Lifelog
     */
    protected $lifelogDao;
    
    /**
     * User DAO
     * 
     * @var Dao_User
     */
    protected $userDao;
    
    /**
     * LifelogComment DAO
     * 
     * @var Dao_LifelogComment
     */
    protected $lifelogCommentDao;
    
    /**
     * LifelogLike DAO
     * 
     * @var Dao_LifelogLike
     */
    protected $lifelogLikeDao;    
    
    
    /**
     * Constructor
     * 
     * @return Business_Lifelog
     */
    public function __construct()
    {        
        $this->userDao = new Dao_User();
        $this->lifelogDao = new Dao_Lifelog();
        $this->lifelogCommentDao = new Dao_LifelogComment();
        $this->lifelogLikeDao = new Dao_LifelogLike();
		
        return $this;
    }
    
    /**
     * Create new life log instance
     * 
     * @param Dto_Lifelog $lifelogDto
     * @param Dto_User $userDto
     * @return array
     */
    public function create(Dto_Lifelog $lifelogDto, Dto_User $userDto)
    {
        $this->lifelogDao->getAdapter()->beginTransaction();
        
        // insert lifelog
    	$lifelogDto->user_id = $userDto->id;
    	$lifelogDto->created_at = Qsoft_Helper_Datetime::current();
    	$this->lifelogDao->insert($lifelogDto);
        
        // update reference in image status table
        if ($lifelogDto->isImageType()) {
            $imageBusiness = new Business_Image();
            $imageBusiness->addLifelogImage($lifelogDto);
        }
    	
        $this->lifelogDao->getAdapter()->commit();
    	return array('status' => true);
    }
    
    /**
     * Delete a lifelog
     * 
     * @param   Dto_Lifelog   $lifelogDto
     * @param   Dto_User        $userDto
     * @return  array
     */
    public function delete(Dto_Lifelog $lifelogDto, Dto_User $userDto)
    {
        
        if ($userDto->id != $lifelogDto->user_id) {
            return array('status' => false, 'error_code' => ERROR_AUTHORIZE_DENY);
        }
        
        $this->lifelogDao->delete($lifelogDto);
        $this->unlink($lifelogDto);
        
        return array('status' => true);
    }
    
    /**
     * Update a lifelog
     * 
     * @param   Dto_Lifelog   $lifelogDto
     * @param   Dto_User        $userDto       
     * @return  array
     */
    public function update(Dto_Lifelog $lifelogDto, Dto_User $userDto)
    {
        if ($userDto->id != $lifelogDto->user_id) {
            return array('status' => false, 'error_code' => ERROR_AUTHORIZE_DENY);
        }
        // get the old image to delete after complete
        $oldLifelogDto = $this->lifelogDao->fetchOne($lifelogDto->id);        
        
        $this->lifelogDao->update($lifelogDto);
        $this->unlink($oldLifelogDto);
        return array('status' => true);
    }
    
    /**
     * Delete a lifelogComment
     * 
     * @param   Dto_LifelogComment   $lifelogCommentDto
     * @param   Dto_User        $userDto
     * @return  array
     */
    public function deleteLifelogComment(Dto_LifelogComment $lifelogCommentDto, Dto_User $userDto)
    {
          
        if ($userDto->id != $lifelogCommentDto->user_id) {
            return array('status' => false, 'error_code' => ERROR_AUTHORIZE_DENY);
        }
        
        $this->lifelogCommentDao->delete($lifelogCommentDto);
        
        return array('status' => true);
    }
    
    /**
     * Create a lifelogComment
     * 
     * @param   Dto_LifelogComment   $lifelogCommentDto       
     * @return  array
     */
    public function createLifelogComment(Dto_LifelogComment $lifelogCommentDto)
    {
    	$lifelogCommentDto->created_at = Qsoft_Helper_Datetime::current();
        $this->lifelogCommentDao->insert($lifelogCommentDto);
        
        return array('status' => true);    
    }
    
    /**
     * Create a lifelogLike
     * 
     * @param   Dto_User        $userDto
     * @param   Dto_LifelogLike   $lifelogLikeDto       
     * @return  array
     */
    public function createLifelogLike(Dto_User $userDto, Dto_LifelogLike $lifelogLikeDto)
    {
        // created or updated lifelogLike
        //$lifelogLikeAvailable = $this->lifelogLikeDao->fetchByUserAndLifelog($userDto->id, $lifelogLikeDto->lifelog_id);
        $lifelogLikeAvailable = $this->lifelogLikeDao->fetchOneBy(array('user_id' => $userDto->id , 'lifelog_id' => $lifelogLikeDto->lifelog_id));
        if( ! $lifelogLikeAvailable) {
            // created
        	$lifelogLikeDto->created_at = Qsoft_Helper_Datetime::current();
            $this->lifelogLikeDao->insert($lifelogLikeDto);       
        } else {
            // updated
            $lifelogLikeDto->id = $lifelogLikeAvailable->id;
            $this->lifelogLikeDao->update($lifelogLikeDto);
        }
        
        return array('status' => true);    
    }
    
    /**
     * Get all lifelogs from given lifelog id
     * 
     * @param Dto_User $userDto
     * @param integer $beginLifelogId
     * @return Qsoft_Db_Table_Rowset
     */
    public function getLifelogs(Dto_User $userDto, $beginLifelogId = null)
    {
        $limit = Zend_Registry::get('app_config')->lifelog->itemPerPage;
        
        return $this->lifelogDao->fetchAllBySubscriptionStatus(
            $userDto->unique_id,
            $beginLifelogId,
            array(Dto_Chat_Roster::SUBSCRIPTION_STATE_BOTH, Dto_Chat_Roster::SUBSCRIPTION_STATE_TO),
            null,
            $limit
        );
    }
    
    /**
     * Unlink image and video of a lifelog
     * 
     * @param Dto_Lifelog $lifelogDto
     * @return void
     */
    public function unlink(Dto_Lifelog $lifelogDto)
    {
        if ($lifelogDto->image) {
            @unlink(realpath($lifelogDto->getImagePath()));            
        }
        if ($lifelogDto->video) {
            @unlink(realpath($lifelogDto->getVideoPath()));            
        }    
    }
}
