<?php


class Form_PointConfig extends Qsoft_Form_Abstract
{

    /**
     * List of dto class for mapping between dto and form
     * 
     * @var array $_dtoClass
     */
    protected $_dtoClass = 'Dto_PointConfig';

    public function __construct($data = null)
    {
        parent::__construct($data);
        
        $this->setAttrib('class', 'index-form');
        
        $this->regist_point = new Zend_Form_Element_Text('regist_point');
        $this->regist_point
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('NotEmpty')
            ->addValidators(array(new Zend_Validate_Int(),
                new Zend_Validate_Between(array('min' => 1, 'max' => 99999999))))
            ->setDecorators(array('ViewHelper', 'Errors'))
            ;
        
        $this->login_point = new Zend_Form_Element_Text('login_point');
        $this->login_point
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('NotEmpty')
            ->addValidators(array(new Zend_Validate_Int(),
                new Zend_Validate_Between(array('min' => 1, 'max' => 99999999))))
            ->setDecorators(array('ViewHelper', 'Errors'))
            ;
        
        $this->submit = App_Form_Factory::submitButton(array('class' => 'confirm'));
        $this->submit->setDecorators(array('ViewHelper'));
        
        if ($data instanceof Qsoft_Dto_Abstract) {
            $this->mapDtoToForm($data);
        }

    }

}
