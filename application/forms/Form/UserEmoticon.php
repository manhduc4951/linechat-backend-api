<?php

/**
 * Form_UserEmoticon classs
 * 
 * @package LineChatApp
 * @subpackage Form
 * @author ducdm
 */
class Form_UserEmoticon extends Qsoft_Form_Abstract
{
    /**
     * The class of DTO object
     * 
     * @var string
     */
    protected $_dtoClass = "Dto_UserEmoticon";
    
    /**
     * Contructor
     * 
     * @param   Dto_UserEmoticon   $data   Dt object
     * @return  Form_UserEmoticon
     */
    public function __construct($data = null)
    {
        
        parent::__construct($data);
        
        $this->feeling_img = new Qsoft_Form_Element_FileImage('feeling_img');
        $this->feeling_img->setLabel('Emoticon Image')
                    //->addValidator('Extension', false, 'png')
                    ->addFilter('UniqueName', Zend_Registry::get('app_config')->user->emoticon->uploadPath);
        if(empty($data->feeling_img)) {
            $this->feeling_img->setRequired(true);    
        }            
        
        if ($this->getStage() == self::STAGE_EDIT) {
            
            if ( ! empty($data->feeling_img)) {
                $this->feeling_img->setImage(Qsoft_Helper_Url::generate(Zend_Registry::get('app_config')->user->emoticon->url.$data->feeling_img));
                $this->feeling_img->setAttrib('alt', "Image not found"); 
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