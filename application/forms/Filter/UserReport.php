<?php

/**
 * Zend Form for search in page user report
 * 
 * @package Forms
 */
class Filter_UserReport extends Qsoft_Form_Abstract
{

    public function __construct($options = array())
    {
        parent::__construct(null, $options);
        
        $this->setAttrib('class', 'search-form mini');
        $this->setMethod('get');
        
        $this->nick_name = new Zend_Form_Element_Text('nick_name');
        $this->nick_name->setLabel('Nick name')
            ->addFilter('StripTags')
            ->addFilter('StringTrim');
        
        $this->created_at = new Qsoft_Form_Element_DateRanger('created_at');
        $this->created_at->setLabel('Date');
        
        $this->submit = App_Form_Factory::doFilterButton();
        
        $this->setDefaultDecorators();
    }

}
