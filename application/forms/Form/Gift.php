<?php

/**
 * Form_Gift classs
 * 
 * @package LineChatApp
 * @subpackage Form
 * @author ducdm
 */
class Form_Gift extends Qsoft_Form_Abstract
{
    /**
     * The class of DTO object
     * 
     * @var string
     */
    protected $_dtoClass = "Dto_Gift";
    
    /**
     * Contructor
     * 
     * @param   Dto_Gift   $data   Dt object
     * @return  Form_Gift
     */
    public function __construct($data = null)
    {
        
        parent::__construct($data);        
        
        $this->gift_title = new Zend_Form_Element_Text('gift_title');
        $this->gift_title
            ->setLabel('Gift name')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('NotEmpty');
        
        $this->gift_img = new Qsoft_Form_Element_FileImage('gift_img');
        $this->gift_img->setLabel('Gift Image')
                    //->addValidator('Extension', false, 'png')                    
                    ->addValidator('NotEmpty')
                    ->addFilter('UniqueName', Zend_Registry::get('app_config')->gift->image->uploadPath);
        if(empty($data->gift_img)) {
            $this->gift_img->setRequired(true);    
        }            
        
        $this->point = new Zend_Form_Element_Text('point');
        $this->point->setLabel('Point')
            ->setRequired(true)
            ->addValidators(array(new Zend_Validate_Int(),
                new Zend_Validate_Between(array('min' => 1, 'max' => 99999999))));
        
        $this->public_date = new ZendX_JQuery_Form_Element_DateTimePicker('public_date');
        $this->public_date->setLabel('Public Date');
        
        if ($this->getStage() == self::STAGE_EDIT) {            
            if (!empty($data->gift_img)) {
                $this->gift_img->setImage(Qsoft_Helper_Url::generate(Zend_Registry::get('app_config')->gift->image->url.$data->gift_img));
                $this->gift_img->setAttrib('alt', "Image not found"); 
            }            
                        
            $this->show_hide = new Zend_Form_Element_Checkbox('show_hide', array('disableHidden' => true));
            $this->show_hide->setLabel('Public / Private')
                            ->setCheckedValue(1)
                            ->setUncheckedValue(0); 
            
            $gift_category_id = Zend_Controller_Front::getInstance()->getRequest()->getParam( 'gift_category_id', null );                            
            $this->back = App_Form_Factory::backButton(Qsoft_Helper_Url::generate(array('controller' => 'gift', 'action' => 'index', 'id' => null)).'?'.http_build_query(array('gift_category_id' => $gift_category_id)));
        }
        
        $this->submit = App_Form_Factory::submitButton(array('class' => 'confirm'));
            
        $this->setDefaultDecorators();
        
        if ($data instanceof Qsoft_Dto_Abstract) {
            $this->mapDtoToForm($data);
        }
    }
}