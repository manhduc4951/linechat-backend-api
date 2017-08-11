<?php

/**
 * Form_Dice classs
 * 
 * @package LineChatApp
 * @subpackage Form
 * @author ducdm
 */
class Form_Dice extends Qsoft_Form_Abstract
{
    /**
     * The class of DTO object
     * 
     * @var string
     */
    protected $_dtoClass = "Dto_Dice";
    
    /**
     * Contructor
     * 
     * @param   Dto_Dice   $data   Dt object
     * @return  Form_Dice
     */
    public function __construct($data = null)
    {
        parent::__construct($data);        
        
        $this->dice_title1 = new Zend_Form_Element_Text('dice_title1');
        $this->dice_title1
            ->setLabel('1')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(false)
            ->setDecorators(array('ViewHelper', 'Errors'))
            ;
            
        $this->dice_title2 = new Zend_Form_Element_Text('dice_title2');
        $this->dice_title2
            ->setLabel('2')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(false)
            ->setDecorators(array('ViewHelper', 'Errors'))
            ;
            
        $this->dice_title3 = new Zend_Form_Element_Text('dice_title3');
        $this->dice_title3
            ->setLabel('3')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(false)
            ->setDecorators(array('ViewHelper', 'Errors'))
            ;    
        
        $this->dice_title4 = new Zend_Form_Element_Text('dice_title4');
        $this->dice_title4
            ->setLabel('4')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(false)
            ->setDecorators(array('ViewHelper', 'Errors'))
            ;
        
        $this->dice_title5 = new Zend_Form_Element_Text('dice_title5');
        $this->dice_title5
            ->setLabel('5')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(false)
            ->setDecorators(array('ViewHelper', 'Errors'))
            ;
        
        $this->dice_title6 = new Zend_Form_Element_Text('dice_title6');
        $this->dice_title6
            ->setLabel('6')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(false)
            ->setDecorators(array('ViewHelper', 'Errors'))
            ;
        
        $this->submit = App_Form_Factory::submitButton(array('class' => 'confirm'));        
        $this->submit->setDecorators(array('ViewHelper'));
        
        if ($data instanceof Qsoft_Dto_Abstract) {
            $this->mapDtoToForm($data);
        }    
    }
    
    
}