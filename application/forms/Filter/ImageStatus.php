<?php

/**
 * Zend Form for search user in page: Manage Image
 * 
 * @package Forms
 */
class Filter_ImageStatus extends Qsoft_Form_Abstract
{

    public function __construct($options = array())
    {
        parent::__construct(null, $options);
        
        $this->setAttrib('class', 'search-form mini');
        $this->setMethod('get');
        
        $this->user_id = new Zend_Form_Element_Text('user_id');
        $this->user_id->setLabel('User ID')
            ->addFilter('StripTags')
            ->addFilter('StringTrim');
            
        $this->type = new Zend_Form_Element_MultiCheckbox('type', array('disableLoadDefaultDecorators' => true));
        $this->type->setLabel('Type')
            ->addMultiOption(Dto_ImageStatus::TYPE_USER, 'User')
            ->addMultiOption(Dto_ImageStatus::TYPE_LIFELOG, 'Lifelog')
            ->addMultiOption(Dto_ImageStatus::TYPE_FILE_TRANSFER, 'FileTransfer')
            ->setSeparator(' ');
        
        $this->created_at = new Qsoft_Form_Element_DateRanger('created_at');
        $this->created_at->setLabel('Registration date');
        
        $this->submit = App_Form_Factory::doFilterButton();
        
        $this->setDefaultDecorators();
    }

}
