<?php

/**
 * Zend Form for search in app start log
 * 
 * @package Forms
 */
class Filter_History extends Qsoft_Form_Abstract
{

    public function __construct($options = array())
    {
        parent::__construct(null,$options);
        
        $this->setAttrib('class', 'search-form mini');
        $this->setMethod('get');
        
        $this->created_at = new Qsoft_Form_Element_DateRanger('created_at');
        $this->created_at->setLabel('Date');
        
        $this->submit = App_Form_Factory::doFilterButton();
        
        $this->setDefaultDecorators();
    }

}
