<?php

/*
 * Login form
 */
class Form_Login extends Qsoft_Form_Abstract
{
    public function init()
    {
        $this->user_name = new Zend_Form_Element_Text('user_name');
        $this->user_name->setLabel("Login ID")
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('StringLength', false, array('min' => 1, 'max' => 50))
            ->setValue("admin");
        
        $this->password = new Zend_Form_Element_Password('password', array('renderPassword' => true));
        $this->password->setLabel("Password")
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('StringLength', false, array('min' => 1, 'max' => 50))
            ->setValue("admin");

        $this->submit = new Zend_Form_Element_Submit('submit');
        $this->submit->setLabel('Login')
            ->setAttrib('class', 'submit-button');

        $this->setDefaultDecorators();
        
    }
}