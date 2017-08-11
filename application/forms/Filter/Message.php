<?php

/**
 * Zend Form for search user to send message
 * 
 * @package Forms
 */
class Filter_Message extends Qsoft_Form_Abstract
{

    public function __construct($options = array())
    {
        parent::__construct(null, $options);
        $this->setAttrib('class', 'search-form');
        $this->setMethod('get');
        
        $this->sex = new Zend_Form_Element_MultiCheckbox('sex');
        $this->sex->setLabel('Sex')
            ->addMultiOption(1, 'Male')
            ->addMultiOption(2, 'Female')            
            ->setSeparator(' ');
        
        $this->created_at = new Qsoft_Form_Element_DateRanger('created_at');
        $this->created_at->setLabel('Registration date');
        
        $this->last_access = new Qsoft_Form_Element_DateRanger('last_access');
        $this->last_access->setLabel('Last access');
        
        $this->point = new Qsoft_Form_Element_TextRanger('point');
        $this->point->setLabel('Point');
        
        $this->sum_purchase = new Qsoft_Form_Element_TextRanger('sum_purchase');
        $this->sum_purchase->setLabel('Price');
        
        $this->submit = App_Form_Factory::doFilterButton();
        
        $this->setTwoColumnDecorators();
    }

}
