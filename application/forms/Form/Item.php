<?php

/**
 * Form_Item classs
 * 
 * @package LineChatApp
 * @subpackage Form
 * @author ducdm
 */
class Form_Item extends Qsoft_Form_Abstract
{
    /**
     * The class of DTO object
     * 
     * @var string
     */
    protected $_dtoClass = "Dto_Item";
    
    /**
     * Contructor
     * 
     * @param   Dto_Item   $data   Dt object
     * @return  Form_Item
     */
    public function __construct($data = null)
    {
        parent::__construct($data);
        
        $this->item_name = new Zend_Form_Element_Text('item_name', array('readonly' => 'readonly'));
        $this->item_name->setLabel('Item name');
        
        $this->item_type = new Zend_Form_Element_Select('item_type');
        $this->item_type->setLabel('Item type')
            ->addMultiOption(Dto_Item::ITEM_TYPE_SEARCH, 'Search')
            ->addMultiOption(Dto_Item::ITEM_TYPE_IMMEDIATE, 'Immediate')
            ->addMultiOption(Dto_Item::ITEM_TYPE_SHAKE,  'Shake')
            ->addMultiOption(Dto_Item::ITEM_TYPE_PROFILE, 'Profile')
            ->addMultiOption(Dto_Item::ITEM_TYPE_TALK_LOG,  'Talk log')
            ;
        
        $this->item_title = new Zend_Form_Element_Text('item_title');
        $this->item_title
            ->setLabel('Item name')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('NotEmpty');
        
        $this->comment = new Zend_Form_Element_Textarea('comment');
        $this->comment->setLabel('Comment')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('style', 'width:90%')
            ->setAttrib('ROWS', '2')
            ->setAttrib('COLS', '0');
            
        
        $this->item_img = new Qsoft_Form_Element_FileImage('item_img');
        $this->item_img->setLabel('Image Upload')                    
                    //->addValidator('Extension', false, 'png')
                    ->addFilter('UniqueName', Zend_Registry::get('app_config')->item->image->uploadPath);                    
        if (empty($data->item_img)) {
            $this->item_img->setRequired(true)
                           ->addValidator('NotEmpty');    
        }            
        if (!empty($data->item_img)) {
            $this->item_img->setImage(Qsoft_Helper_Url::generate(Zend_Registry::get('app_config')->item->image->url.$data->item_img));
            $this->item_img->setAttrib('alt', "Image not found"); 
        }
        
        $this->point = new Zend_Form_Element_Text('point');
        $this->point->setLabel('Point')
            ->setRequired(true)
            ->addValidators(array(new Zend_Validate_Int(),
                new Zend_Validate_Between(array('min' => 1, 'max' => 99999999))));
                            
        $this->public_date = new ZendX_JQuery_Form_Element_DateTimePicker('public_date');
        $this->public_date->setLabel('Public Date');
        
        if ($this->getStage() == self::STAGE_EDIT) {
            $this->show_hide = new Zend_Form_Element_Checkbox('show_hide', array('disableHidden' => true));
            $this->show_hide->setLabel('Public / Private')
                            ->setCheckedValue(1)
                            ->setUncheckedValue(0);
                
            $this->back = App_Form_Factory::backButton();
        }
        
        $this->public_date = new ZendX_JQuery_Form_Element_DateTimePicker('public_date');
        $this->public_date->setLabel('Public Date');         
        
        $this->submit = App_Form_Factory::submitButton(array('class' => 'confirm'));
        
        $this->setDefaultDecorators();
        
        if ($data instanceof Qsoft_Dto_Abstract) {
            $this->mapDtoToForm($data);
        }    
    }
    
    
}