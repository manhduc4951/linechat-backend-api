<?php

/**
 * Zend Form for Acl Role
 * 
 * @package Forms
 */
class Form_AclRole extends Qsoft_Form_Abstract
{

    /**
     * List of dto class for mapping between dto and form
     * 
     * @var array $_dtoClass
     */
    protected $_dtoClass = 'Dto_AclRole';

    public function __construct($data = null)
    {
        parent::__construct($data);
        
        $this->role_id = new Zend_Form_Element_Select('role_id');
        $this->role_id->setLabel('Role ID')
                      ->setDecorators(array('ViewHelper', 'Errors'));
        $aclRoleDao = new Dao_AclRole();
        foreach($aclRoleDao->fetchAll() as $aclRole)
        {
            $this->role_id->addMultiOption($aclRole->role_id, $aclRole->role_id);
        }
        
        $this->role_name = new Zend_Form_Element_Text('role_name');
        $this->role_name->setLabel('Role name')
             ->addFilter('StripTags')
             ->addFilter('StringTrim')
             ->setRequired(true)
             ->addValidator('NotEmpty')
             ->setDecorators(array('ViewHelper', 'Errors'));
        
        $this->submit = App_Form_Factory::submitButton(array('class' => 'confirm'));
        $this->submit->setDecorators(array('ViewHelper'));

        //$this->setDefaultDecorators();

        if ($data instanceof Qsoft_Dto_Abstract) {
            $this->mapDtoToForm($data);
        }
    }
    
}