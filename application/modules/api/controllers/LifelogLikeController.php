<?php

class Api_LifelogLikeController extends Qsoft_Rest_Controller
{   
    protected $_businessClass = 'Business_Lifelog';
    protected $_daoClass = 'Dao_LifelogLike';
    
    public function postAction()
    {
        $userDto = Zend_Registry::get('api_user');
        $data = $this->getRequest()->getPost();
        $form = new Form_LifelogLike;

        // validate the post parameters
        if (!$form->isValid($data)) {
            $errors = $form->getErrors();
            Zend_Registry::get('log')->log("post lifelog like failure: ". print_r($errors, true), Zend_Log::ERR);
            
            if (in_array(Zend_Validate_Db_Abstract::ERROR_NO_RECORD_FOUND, $errors['lifelog_id'])) {
                $this->failure(ERROR_LIFELOG_NOT_FOUND);
            }
            
            if (in_array(Zend_Validate_Callback::INVALID_VALUE, $errors['lifelog_id'])) {
                $this->failure(ERROR_LIFELOG_LIKE_TWICE);
            }
            
            $this->failure(ERROR_FORM_VALIDATION_FAILURE);
        }

        $lifelogLikeDto = $form->mapFormToDto();
        $lifelogLikeDto->user_id = $userDto->id;        
        
        $result = $this->getBusiness()->createLifelogLike($userDto, $lifelogLikeDto);
        if (true !== $result['status']) {
            $this->fromArray($result);
        }
        
        $this->success(array('lifelog_like' => array_merge($lifelogLikeDto->toEndUserArray(), $userDto->toContactArray())));
    }
    
}
