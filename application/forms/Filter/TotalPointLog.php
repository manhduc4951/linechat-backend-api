<?php

/**
 * Zend Form for search in page total point log
 * 
 * @package Forms
 */
class Filter_TotalPointLog extends Qsoft_Form_Abstract
{

    public function __construct($options = array())
    {
        parent::__construct(null, $options);
        
        $this->setAttrib('class', 'search-form mini');
        $this->setMethod('get');
        
        $this->date_hour = new Qsoft_Form_Element_DateRanger('date_hour');
        $this->date_hour->setLabel('Date');
        
        $this->submit = App_Form_Factory::doFilterButton();
        
        $this->setDefaultDecorators();
    }

}
