<?php

/**
 * Form_ApiLogin classs
 * 
 * @package Form
 * @author duyld
 */
class Form_ApiLogin extends Qsoft_Form_Abstract
{
    
    public function init()
    {
        $this->unique_id = new Zend_Form_Element_Text('unique_id');
        $this->unique_id
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('NotEmpty')
        ;

        $this->longitude = App_Form_Factory::coordinateElement('longitude');
        $this->latitude = App_Form_Factory::coordinateElement('latitude');
    }

}