<?php

/**
 * Form_LifelogComment classs
 * 
 * @package LineChatApp
 * @subpackage Form
 * @author ducdm
 */
class Form_LifelogComment extends Qsoft_Form_Abstract
{
    /**
     * The class of DTO object
     * 
     * @var string
     */
    protected $_dtoClass = "Dto_LifelogComment";
    
    /**
     * Contructor
     * 
     * @param   Dto_LifelogComment   $data   Dt object
     * @return  Form_LifelogComment
     */
    public function __construct($data = null)
    {
        parent::__construct($data);
        
        $this->lifelog_id = App_Form_Factory::lifelog();
        
        $this->comment = new Zend_Form_Element_Text('comment');
        $this->comment
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('NotEmpty'); 
        
        return $this;
    }
}