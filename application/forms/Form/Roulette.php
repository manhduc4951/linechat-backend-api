<?php

/**
 * Form_Roulette classs
 * 
 * @package LineChatApp
 * @subpackage Form
 * @author ducdm
 */
class Form_Roulette extends Qsoft_Form_Abstract
{
    /**
     * The class of DTO object
     * 
     * @var string
     */
    protected $_dtoClass = "Dto_Roulette";
    
    /**
     * Contructor
     * 
     * @param   Dto_Roulette   $data   Dt object
     * @return  Form_Roulette
     */
    public function __construct($data = null)
    {
        
        parent::__construct($data);
        
        $this->title = new Zend_Form_Element_Text('title');
        $this->title->addFilter('StringTrim')
                    ->addFilter('StripTags')
                    ->setDecorators(array('ViewHelper', 'Errors'));
        
        $this->point = new Zend_Form_Element_Text('point');
        $this->point->addValidators(array(new Zend_Validate_Int(),
                      new Zend_Validate_Between(array('min' => 1, 'max' => 99999999))))
                    ->setDecorators(array('ViewHelper', 'Errors'));
        
        $this->odds = new Zend_Form_Element_Text('odds');
        $this->odds->addValidators(array(new Zend_Validate_Int(),
                      new Zend_Validate_Between(array('min' => 0, 'max' => 100))))
                    ->setDecorators(array('ViewHelper', 'Errors'));
        
        $this->submit = App_Form_Factory::submitButton(array('class' => 'confirm'));
        $this->submit->setDecorators(array('ViewHelper'));
        
        if ($data instanceof Qsoft_Dto_Abstract) {
            $this->mapDtoToForm($data);
        }
    }
}