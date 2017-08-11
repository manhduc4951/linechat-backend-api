<?php

/**
 * Form_UserAvatar classs
 * 
 * @package LineChatApp
 * @subpackage Form
 * @author ducdm
 */
class Form_UserAvatar extends Qsoft_Form_Abstract
{
    /**
     * The class of DTO object
     * 
     * @var string
     */
    protected $_dtoClass = "Dto_UserAvatar";
    
    /**
     * Contructor
     * 
     * @param   Dto_UserAvatar   $data   Dt object
     * @return  Form_UserAvatar
     */
    public function __construct($data = null)
    {
        
        parent::__construct($data);
        
        $this->sex = new Zend_Form_Element_Radio('sex');
        $this->sex->setLabel('Sex')
            ->setRequired(true)
            ->addMultiOption(Dto_User::GENDER_MALE, 'Male')  
            ->addMultiOption(Dto_User::GENDER_FEMALE, 'Female') 
	        ->setValue(Dto_User::GENDER_MALE) 
            ->setSeparator(' ');
        
        $this->avatar_img = new Qsoft_Form_Element_FileImage('avatar_img');
        $this->avatar_img->setLabel('Avatar Image')
                    //->addValidator('Extension', false, 'png')                    
                    ->addValidator('NotEmpty')
                    ->addFilter('UniqueName', Zend_Registry::get('app_config')->user->avatar->uploadPath);
        if(empty($data->avatar_img)) {
            $this->avatar_img->setRequired(true);    
        }            
        
        if ($this->getStage() == self::STAGE_EDIT) {
            
            if ( ! empty($data->avatar_img)) {
                $this->avatar_img->setImage(Qsoft_Helper_Url::generate(Zend_Registry::get('app_config')->user->avatar->url.$data->avatar_img));
                $this->avatar_img->setAttrib('alt', "Image not found"); 
            }            
                        
            $this->show_hide = new Zend_Form_Element_Checkbox('show_hide', array('disableHidden' => true));
            $this->show_hide->setLabel('Public / Private')
                            ->setCheckedValue(1)
                            ->setUncheckedValue(0); 
                                        
            $this->back = App_Form_Factory::backButton();
        }
        
        $this->submit = App_Form_Factory::submitButton(array('class' => 'confirm'));
            
        $this->setDefaultDecorators();
        
        if ($data instanceof Qsoft_Dto_Abstract) {
            $this->mapDtoToForm($data);
        }
    }
}