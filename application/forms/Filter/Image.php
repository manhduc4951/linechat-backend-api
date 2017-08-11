<?php

/**
 * Zend Form for search user to send message
 * 
 * @package Forms
 */
class Filter_Image extends Qsoft_Form_Abstract
{

    public function __construct($options = array())
    {
        $this->setAttrib('class', 'search-form');
        $this->setMethod('get');
        
        $this->user_id = new Zend_Form_Element_Text('user_id');
        $this->user_id->setLabel('● User ID')
            ->addFilter('StripTags')
            ->addFilter('StringTrim');
        
        $this->submit = App_Form_Factory::doFilterButton();
        
        $this->setTwoColumnDecorators();
    }

}
