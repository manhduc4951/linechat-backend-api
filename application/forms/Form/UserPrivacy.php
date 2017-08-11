<?php

/**
 * Form_UserPrivacy classs
 * 
 * @package LineChatApp
 * @subpackage Form
 * @author duyld
 */
class Form_UserPrivacy extends Qsoft_Form_Abstract
{
    /**
     * The class of DTO object
     * 
     * @var string
     */
    protected $_dtoClass = "Dto_User";
    
    /**
     * Contructor
     * 
     * @param   Dto_UserGroup   $data   Dt object
     * @return  Form_UserGroup
     */
    public function __construct($data = null)
    {
        parent::__construct($data);
        
        $this->is_allow_call_by_search = App_Form_Factory::booleanElement('is_allow_call_by_search');
        $this->is_allow_find_by_id = App_Form_Factory::booleanElement('is_allow_find_by_id');
        $this->is_allow_find_by_shake = App_Form_Factory::booleanElement('is_allow_find_by_shake');
        $this->is_allow_talk_by_search = App_Form_Factory::booleanElement('is_allow_talk_by_search');
        $this->is_passcode_enable = App_Form_Factory::booleanElement('is_passcode_enable');
        
        $this->passcode = new Zend_Form_Element_Text('passcode');
        $this->passcode
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(false)
            ->addValidator('StringLength', false, array('max' => 4, 'min' => 4))
            ->addValidator('Alnum')
        ;
    }
    
}