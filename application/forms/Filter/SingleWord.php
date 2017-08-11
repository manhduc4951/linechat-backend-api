<?php

/**
 * Zend Form for search Single Word
 * 
 * @package Forms
 */
class Filter_SingleWord extends Qsoft_Form_Abstract
{

    public function __construct($options = array())
    {
        parent::__construct(null, $options);
        
        $this->setAttrib('class', 'search-form mini');
        $this->setMethod('get');
        
        $this->description = new Zend_Form_Element_Text('description');
        $this->description->setLabel('Word')
            ->addFilter('StripTags')
            ->addFilter('StringTrim');
        
        $this->created_at = new Qsoft_Form_Element_DateRanger('created_at');
        $this->created_at->setLabel('Date');
        
        $this->submit = App_Form_Factory::doFilterButton();
        
        $this->setDefaultDecorators();
    }

}
