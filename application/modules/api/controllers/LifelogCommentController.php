<?php

class Api_LifelogCommentController extends Qsoft_Rest_Controller
{    
    protected $_businessClass = 'Business_Lifelog';
    protected $_daoClass = 'Dao_LifelogComment';
    
    public function postAction()
    {
        $userDto = Zend_Registry::get('api_user');
        $data = $this->getRequest()->getPost();
        $form = new Form_LifelogComment;

        // validate the post parameters
        if (!$form->isValid($data)) {
            $errors = $form->getErrors();
            Zend_Registry::get('log')->log("post lifelog comment failure: ". print_r($errors, true), Zend_Log::ERR);
            
            if (in_array(Zend_Validate_Db_Abstract::ERROR_NO_RECORD_FOUND, $errors['lifelog_id'])) {
                $this->failure(ERROR_LIFELOG_NOT_FOUND);
            }
            
            $this->failure(ERROR_FORM_VALIDATION_FAILURE);
        }

        $lifelogCommentDto = $form->mapFormToDto();
        $lifelogCommentDto->user_id = $userDto->id;
        
        $result = $this->getBusiness()->createLifelogComment($lifelogCommentDto);

        if (true !== $result['status']) {
            $this->fromArray($result);
        }

        $this->success(array('lifelog_comment' => $lifelogCommentDto->toEndUserArray()));
    }
    public function deleteAction()
    {
        $lifelogCommentDto = $this->getLifelogComment();        
        $userDto = Zend_Registry::get('api_user');
        $this->fromArray($this->getBusiness()->deleteLifelogComment($lifelogCommentDto, $userDto));
    }
    protected function getLifelogComment()
    {
        $id = $this->_getParam('id', '');
        $lifelogCommentDto = $this->getDao()->fetchOneBy('id', $id) OR $this->failure(ERROR_LIFELOG_COMMENT_NOT_FOUND);
        
        return $lifelogCommentDto;
    }
}
