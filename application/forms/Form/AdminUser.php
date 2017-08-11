<?php

/**
 * Zend Form for Admin User
 * 
 * @package Forms
 */
class Form_AdminUser extends Qsoft_Form_Abstract
{

    /**
     * List of dto class for mapping between dto and form
     * 
     * @var array $_dtoClass
     */
    protected $_dtoClass = 'Dto_AdminUser';

    public function __construct($data = null)
    {
        parent::__construct($data);
        
        $this->login_id = new Zend_Form_Element_Text('login_id');
        $this->login_id->setLabel('Login ID')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('NotEmpty')
            ->addValidator('Db_NoRecordExists', false, array('table' => 'admin_user', 'field' => 'login_id')); 
        if(isset($data))
        {
        $clause = 'admin_user_id != ' . $data->admin_user_id;
        $this->login_id->addValidator('Db_NoRecordExists', false, array('table' => 'admin_user', 'field' => 'login_id', 'exclude' => $clause));    
        } 
            
        $this->admin_user_name = new Zend_Form_Element_Text('admin_user_name');
        $this->admin_user_name->setLabel('Admin user name')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('NotEmpty');
        
        $this->password = new Zend_Form_Element_Text('password');
        $this->password->setLabel('Password')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('NotEmpty');
        
        $this->admin_role_id = new Zend_Form_Element_Select('admin_role_id');
        $this->admin_role_id->setLabel('Role')
                              ;
        $aclRoleDao = new Dao_AclRole();
        foreach($aclRoleDao->fetchAll() as $aclRole)
        {
            $this->admin_role_id->addMultiOption($aclRole->role_id, $aclRole->role_name);
        }
        
        $this->last_access = new Zend_Form_Element_Text('last_access', array('readonly' => 'readonly'));
        $this->last_access->setLabel('Last access');
        
        $this->work_log = new Qsoft_Form_Element_Link('work_log');
        $this->work_log->setAttribs(array('url' => Qsoft_Helper_Url::generate(array('controller' => 'admin-work-log', 'action' => 'index'))))->setValue('Work log');
        
        $this->login_log = new Qsoft_Form_Element_Link('login_log');
        $this->login_log->setAttribs(array('url' => Qsoft_Helper_Url::generate(array('controller' => 'admin-login-log', 'action' => 'index'))))->setValue('Login log');
        
        if ($this->getStage() == self::STAGE_EDIT) {
            $this->back = App_Form_Factory::backButton();
            $this->login_id->setAttrib('readonly','readonly');
            $this->delete = new Zend_Form_Element_Button('delete');
            $this->delete->setLabel('Delete')
                                    ->setAttrib('class',Qsoft_Helper_Url::generate(array('action'=>'delete', 'id' => $data->admin_user_id)));
            //disabled delete button when user don't have enough role or user try to delete themselves
            if (Zend_Auth::getInstance()->getIdentity()->admin_user_id == $data->admin_user_id
                or Zend_Auth::getInstance()->getIdentity()->admin_role_id >= $data->admin_role_id
               )
            {
                $this->delete->setAttrib('disabled','disabled');
            }                        
        }
        
        $this->submit = App_Form_Factory::submitButton(array('class' => 'confirm'));

        $this->setDefaultDecorators();
        
        $this->work_log->setDecorators(array(
            'ViewHelper',
            array(array('data' => 'HtmlTag'), array(
                'tag' => 'td', 'colspan' => 2, 'class' => 'submit-td', 'openOnly' => true
            )),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true))
        ));
        
        $this->login_log->setDecorators(array(
            'ViewHelper',
            array(array('data' => 'HtmlTag'), array(
                'tag' => 'td', 'closeOnly' => true
            )),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true))
        ));

        if ($data instanceof Qsoft_Dto_Abstract) {
            $this->mapDtoToForm($data);
        }
    }
    
}