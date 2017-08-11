<?php

/**
 * Form_Lifelog classs
 * 
 * @package LineChatApp
 * @subpackage Form
 * @author ducdm
 */
class Form_Lifelog extends Qsoft_Form_Abstract
{
    /**
     * The class of DTO object
     * 
     * @var string
     */
    protected $_dtoClass = "Dto_Lifelog";
    
    /**
     * Contructor
     * 
     * @param   Dto_Lifelog   $data   Dt object
     * @return  Form_Lifelog
     */
    public function __construct($data = null)
    {
        parent::__construct($data);
        $this->setEnctype(Zend_Form::ENCTYPE_MULTIPART);
        
        $this->message = new Zend_Form_Element_Text('message');
        $this->message
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(false);
            
        $this->sticker = new Zend_Form_Element_Text('sticker');
        $this->sticker
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(false); 
               
        $this->longitude = new Zend_Form_Element_Text('longitude');
        $this->longitude
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addFilter('Null')
            ->setRequired(false);
            
        $this->latitude = new Zend_Form_Element_Text('latitude');
        $this->latitude
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addFilter('Null')
            ->setRequired(false);         
        
        $imageConfig = Zend_Registry::get('app_config')->lifelog->image;
        $this->image = App_Form_Factory::imageUpload($imageConfig);
        
        $videoConfig = Zend_Registry::get('app_config')->lifelog->video;
        $this->video = App_Form_Factory::videoUpload($videoConfig);
        
        return $this;
    }
    
    /**
     * Do rollback action if form is failure to perform
     * 
     * @return Form_Lifelog
     */
    public function rollback()
    {
        if ($imagePath = $this->image->getFilename()) {
            @unlink($imagePath);
        }
        if ($videoPath = $this->video->getFilename()) {
            @unlink($videoPath);
        }
        
        return $this;
    }
    
}