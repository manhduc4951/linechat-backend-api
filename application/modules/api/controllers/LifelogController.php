<?php

class Api_LifelogController extends App_Rest_Controller
{
    protected $_businessClass = 'Business_Lifelog';
    protected $_daoClass = 'Dao_Lifelog';
    protected $daoLifelogComment;
    protected $daoLifelogLike;
    
    public function init()
    {
        $this->daoLifelogComment = new Dao_LifelogComment();
        $this->daoLifelogLike    = new Dao_LifelogLike();
        parent::init();
    }
    
    /**
     * Get the Dao
     *
     * @return Dao_Lifelog
     */
    protected function getDao()
    {
        return parent::getDao();
    }
    
    /**
     * Get the business model
     *
     * @return Business_Lifelog
     */
    protected function getBusiness()
    {
        return parent::getBusiness();
    }
    
    /**
     * Get all lifelog of an user
     */
    public function indexAction()
    {
        $uniqueId = $this->_getParam('unique_id', null);
        if (null === $uniqueId) {
            $userDto = Zend_Registry::get('api_user');
        } else {
            $userDto = $this->getUser($uniqueId);
        }
        
        $lifelogs = $this->getDao()->fetchAllByUser($userDto->id);

        $this->success(array('lifelogs' => $lifelogs->toEndUserArray()));    
    }
    
    /**
     * Get lifelog of all friends
     */
    public function friendAction()
    {
        $beginLifelogId = (int) $this->_getParam('begin_id', 0);
        $userDto = Zend_Registry::get('api_user');
        
        $lifelogs = $this->getBusiness()->getLifelogs($userDto, $beginLifelogId);
        $this->success(array('lifelogs' => $lifelogs->toEndUserArray()));
    }
    
    /**
     * Get an life log details
     */
    public function getAction()
    {
        $id = $this->_getParam('id', '');
        $lifelogDto = $this->getDao()->fetchOneWithUser($id);
        //var_dump($lifelogDto);die;
        
        if ( ! $lifelogDto) {
            $this->failure(ERROR_LIFELOG_NOT_FOUND);
        }
        
        $lifelogComments = $this->daoLifelogComment->fetchAllWithUserBy('lifelog_id', $id);
        foreach ($lifelogComments as $index => $lifelogComment) {
            $lifelogComments[$index] = Qsoft_Helper_Array::extract(
                array('id', 'comment', 'created_at', 'user_id', 'nick_name', 'unique_id'),
                $lifelogComment
            );
            $lifelogComments[$index]['small_image'] = Dto_User::smallImageUrl($lifelogComment['user_img']);
        }
        
        $lifelogLikes    = $this->daoLifelogLike->fetchAllWithUserBy('lifelog_id', $id);
        foreach ($lifelogLikes as $index => $lifelogLike) {
            $lifelogLikes[$index] = Qsoft_Helper_Array::extract(
                array('id', 'sticker', 'user_id', 'nick_name', 'unique_id'),
                $lifelogLike
            );
            $lifelogLikes[$index]['small_image'] = Dto_User::smallImageUrl($lifelogLike['user_img']);
        }
        
        $this->success(array('lifelog' => $lifelogDto->toEndUserArray(),
            'lifelog_comment' => array_values($lifelogComments),
            'lifelog_like' => array_values($lifelogLikes),
        ));
    }
    public function postAction()
    {
        $userDto = Zend_Registry::get('api_user');
        $data = $this->getRequest()->getPost();
        $form = new Form_Lifelog;

        // validate the post parameters
        if (!$form->isValid($data)) {
            $errors = $form->getErrors();
            Zend_Registry::get('log')->log("post lifelog failure: ". print_r($errors, true), Zend_Log::ERR);
            $this->failure(ERROR_FORM_VALIDATION_FAILURE);
        }

        $lifelogDto = $form->mapFormToDto();
        $result = $this->getBusiness()->create($lifelogDto, $userDto);
        if (true !== $result['status']) {
        	$this->fromArray($result);
        }

        $this->success(array('lifelog' => $lifelogDto->toEndUserArray()));
    }
    
    public function deleteAction()
    {
        $lifelogDto = $this->getLifelog();
        $userDto = Zend_Registry::get('api_user');
        $this->fromArray($this->getBusiness()->delete($lifelogDto, $userDto));
    }
    protected function getLifelog()
    {
        $id = $this->_getParam('id', '');
        $lifelogDto = $this->getDao()->fetchOneBy('id', $id) OR $this->failure(ERROR_LIFELOG_NOT_FOUND);
        
        return $lifelogDto;
    }
    public function putAction()
    {
        // retrieve the group to modify
        $lifelogDto = $this->getLifelog();
        
        // validate the submitted parameters
        $data = $this->getRequest()->getPost();
        $form = new Form_Lifelog($lifelogDto);
        
        // validate the post parameters
        if ( ! $form->isValid($data)) {
            Zend_Registry::get('log')->log("update lifelog failure: ". print_r($form->getErrors(), true), Zend_Log::ERR);
            $this->failure(ERROR_FORM_VALIDATION_FAILURE);
        }
        
        // let business perform the action
        $userDto = Zend_Registry::get('api_user');
        $lifelogDto = $form->mapFormToDto($lifelogDto);
        $result = $this->getBusiness()->update($lifelogDto, $userDto);
        
        if ($result['status'] !== true) {
            return $this->fromArray($result);
        }
        
        $this->success(array('lifelog' => $lifelogDto->toEndUserArray()));
    }
    
}
