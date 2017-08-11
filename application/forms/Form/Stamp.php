<?php

/**
 * Form_Stamp classs
 * 
 * @package LineChatApp
 * @subpackage Form
 * @author ducdm
 */
class Form_Stamp extends Qsoft_Form_Abstract
{
    /**
     * The class of DTO object
     * 
     * @var string
     */
    protected $_dtoClass = "Dto_Stamp";
    
    /**
     * Contructor
     * 
     * @param   Dto_Stamp   $data   Dt object
     * @return  Form_Stamp
     */
    public function __construct($data = null)
    {
        
        parent::__construct($data);
        
        $this->stamp_name = new Zend_Form_Element_Text('stamp_name');
        $this->stamp_name->setLabel('Stamp name')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('NotEmpty');
        
        $this->stamp_description = new Zend_Form_Element_Textarea('stamp_description');
        $this->stamp_description->setLabel('Stamp Description')
            ->setAttrib('style', 'width:90%')
            ->setAttrib('ROWS', '5')
            ->setAttrib('COLS', '25')
            ->addFilter('StripTags')
            ->addFilter('StringTrim');
        
        $this->stamp_small_image = new Qsoft_Form_Element_FileImage('stamp_small_image');
        $this->stamp_small_image->setLabel('Stamp small image')
                    //->addValidator('Extension', false, 'png')                    
                    ->addValidator('NotEmpty')                    
                    ->addFilter('UniqueName', Zend_Registry::get('app_config')->stamp->image->small->uploadPath);
        if(empty($data->stamp_small_image)) {
            $this->stamp_small_image->setRequired(true);    
        }
        
        
        $this->stamp_large_image = new Qsoft_Form_Element_FileImage('stamp_large_image');
        $this->stamp_large_image->setLabel('Stamp large image')
                    //->addValidator('Extension', false, 'png')                    
                    ->addValidator('NotEmpty')
                    ->addFilter('UniqueName', Zend_Registry::get('app_config')->stamp->image->large->uploadPath);
        if(empty($data->stamp_large_image)) {
            $this->stamp_large_image->setRequired(true);    
        }
        
        $this->stamp_icon = new Qsoft_Form_Element_FileImage('stamp_icon');
        $this->stamp_icon->setLabel('Stamp icon')
                    //->addValidator('Extension', false, 'png')                    
                    ->addValidator('NotEmpty')
                    ->addFilter('UniqueName', Zend_Registry::get('app_config')->stamp->image->icon->uploadPath);
        if(empty($data->stamp_icon)) {
            $this->stamp_icon->setRequired(true);    
        }
        
        $this->stamp_zip_package = new Zend_Form_Element_File('stamp_zip_package');
        $this->stamp_zip_package->setLabel('Stamp zip')
                    ->addValidator('Extension', false, 'zip')                    
                    ->addValidator('NotEmpty')
                    ->addFilter('UniqueName', Zend_Registry::get('app_config')->stamp->zip->uploadPath);
        if(empty($data->stamp_zip_package)) {
            $this->stamp_zip_package->setRequired(true);    
        }
        
        $this->point = new Zend_Form_Element_Text('point');
        $this->point->setLabel('Point')
            ->setRequired(true)
            ->addValidators(array(new Zend_Validate_Int(),
                new Zend_Validate_Between(array('min' => 1, 'max' => 99999999))));
        
        $this->public_date = new ZendX_JQuery_Form_Element_DateTimePicker('public_date');
        $this->public_date->setLabel('Public Date');            
        
        if ($this->getStage() == self::STAGE_EDIT) {
            if ( ! empty($data->stamp_small_image)) {
                $this->stamp_small_image->setImage(Qsoft_Helper_Url::generate(Zend_Registry::get('app_config')->stamp->image->small->url.'/'.$data->stamp_small_image));
                $this->stamp_small_image->setAttrib('alt', "Image not found"); 
            }
            if ( ! empty($data->stamp_icon)) {
                $this->stamp_icon->setImage(Qsoft_Helper_Url::generate(Zend_Registry::get('app_config')->stamp->image->icon->url.'/'.$data->stamp_icon));
                $this->stamp_icon->setAttrib('alt', "Image not found"); 
            }
            if ( ! empty($data->stamp_large_image)) {
                $this->stamp_large_image->setImage(Qsoft_Helper_Url::generate(Zend_Registry::get('app_config')->stamp->image->large->url.'/'.$data->stamp_large_image));
                $this->stamp_large_image->setAttrib('alt', "Image not found"); 
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
    
    /**
     * Do rollback action if form is failure to perform
     * 
     * @return Form_Stamp
     */
    public function rollback()
    {
        if ($imageSmallPath = $this->stamp_small_image->getFilename()) {            
            @unlink($imageSmallPath);
        }
        if ($imageLargePath = $this->stamp_large_image->getFilename()) {
            @unlink($imageLargePath);
        }
        if ($imageIcon = $this->stamp_icon->getFilename()) {            
            @unlink($imageIcon);
        }
        
        return $this;
    }
}