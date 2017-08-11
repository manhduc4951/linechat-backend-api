<?php

/**
 * Form_LifelogLike classs
 * 
 * @package LineChatApp
 * @subpackage Form
 * @author ducdm
 */
class Form_LifelogLike extends Qsoft_Form_Abstract
{
    /**
     * The class of DTO object
     * 
     * @var string
     */
    protected $_dtoClass = "Dto_LifelogLike";
    
    /**
     * Contructor
     * 
     * @param   Dto_LifelogLike   $data   Dt object
     * @return  Form_LifelogLike
     */
    public function __construct($data = null)
    {
        parent::__construct($data);
        
        $this->lifelog_id = App_Form_Factory::lifelog();
        
        // add validator for make sure the user can like each lifelog once time
        $this->lifelog_id->addValidator('Callback', false, function($value) {
            $adapter = Zend_Registry::get('mainAdapter');
            $userDto = Zend_Registry::get('api_user');
            
            $select = $adapter->select()
                ->from('lifelog_like')
                ->where('user_id = ?', $userDto->id)
                ->where('lifelog_id = ?', $value);
            
            return ! (boolean) $adapter->fetchRow($select);
        });
            
        $this->sticker = new Zend_Form_Element_Text('sticker');
        $this->sticker
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('NotEmpty')
        ;
        
        return $this;
    }
}