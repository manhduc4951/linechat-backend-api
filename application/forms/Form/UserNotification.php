<?php

/**
 * Form_UserNotification classs
 * 
 * @package LineChatApp
 * @subpackage Form
 * @author duyld
 */
class Form_UserNotification extends Qsoft_Form_Abstract
{
    /**
     * The class of DTO object
     * 
     * @var string
     */
    protected $_dtoClass = "Dto_User";
    
    /**
     * Contructor
     * 
     * @param   Dto_UserGroup   $data   Dt object
     * @return  Form_UserGroup
     */
    public function __construct($data = null)
    {
        parent::__construct($data);
        
        $this->is_allow_notification = App_Form_Factory::booleanElement('is_allow_notification');
        $this->mute_notify = new Zend_Form_Element_Text('mute_notify');
        $this->tone_id = new Zend_Form_Element_Text('tone_id');
        $this->is_new_message_notify = App_Form_Factory::booleanElement('is_new_message_notify');
        $this->is_show_preview = App_Form_Factory::booleanElement('is_show_preview');
        $this->is_allow_group_invitation = App_Form_Factory::booleanElement('is_allow_group_invitation');
        $this->is_allow_in_app_alert = App_Form_Factory::booleanElement('is_allow_in_app_alert');
        $this->is_allow_in_app_sound = App_Form_Factory::booleanElement('is_allow_in_app_sound');
        $this->is_allow_in_app_vibration = App_Form_Factory::booleanElement('is_allow_in_app_vibration');
        $this->is_call_receive = App_Form_Factory::booleanElement('is_call_receive');
    }
    
}