<?php
/**
 * Form_ContentsLimit classs
 * 
 * @package LineChatApp
 * @subpackage Form
 * @author ducdm
 */
class Form_ContentsLimit extends Qsoft_Form_Abstract
{

    /**
     * List of dto class for mapping between dto and form
     * 
     * @var array $_dtoClass
     */
    protected $_dtoClass = 'Dto_ContentsLimit';

    public function __construct($data = null)
    {
        parent::__construct($data);
        
        $this->setAttrib('class', 'index-form');
        
        $this->available_count = new Zend_Form_Element_Text('available_count');
        $this->available_count
            ->setLabel('Available count')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('NotEmpty')
            ->addValidators(array(new Zend_Validate_Int(),
                new Zend_Validate_Between(array('min' => 1, 'max' => 99999999))))
            ->setDecorators(array('ViewHelper', 'Errors'));
        
        $this->recovery_time = new Zend_Form_Element_Text('recovery_time');
        $this->recovery_time
            ->setLabel('Recovery time')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('NotEmpty')
            ->addValidators(array(new Zend_Validate_Int(),
                new Zend_Validate_Between(array('min' => 1, 'max' => 99999999))))
            ->setDecorators(array('ViewHelper', 'Errors'));             
        
        $this->recovery_amount = new Zend_Form_Element_Text('recovery_amount');
        $this->recovery_amount
            ->setLabel('Recovery amount')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('NotEmpty')
            ->addValidators(array(new Zend_Validate_Int(),
                new Zend_Validate_Between(array('min' => 1, 'max' => 99999999))))
            ->setDecorators(array('ViewHelper', 'Errors'));    
        
        $this->now_init_count = new Zend_Form_Element_Text('now_init_count');
        $this->now_init_count
            ->setLabel('Now init count')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('NotEmpty')
            ->addValidators(array(new Zend_Validate_Int(),
                new Zend_Validate_Between(array('min' => 1, 'max' => 99999999))))
            ->setDecorators(array('ViewHelper', 'Errors'));    
        
        $this->submit = App_Form_Factory::submitButton(array('class' => 'confirm'));        
        $this->submit->setDecorators(array('ViewHelper'));
        
        if ($data instanceof Qsoft_Dto_Abstract) {
            $this->mapDtoToForm($data);
        }

    }

}
