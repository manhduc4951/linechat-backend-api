<?php

/**
 * Form_UserGroup classs
 * 
 * @package LineChatApp
 * @subpackage Form
 * @author duyld
 */
class Form_UserGroup extends Qsoft_Form_Abstract
{
    /**
     * The class of DTO object
     * 
     * @var string
     */
    protected $_dtoClass = "Dto_UserGroup";
    
    /**
     * Contructor
     * 
     * @param   Dto_UserGroup   $data   Dt object
     * @return  Form_UserGroup
     */
    public function __construct($data = null)
    {
        parent::__construct($data);
        $this->setEnctype(Zend_Form::ENCTYPE_MULTIPART);
        
        $this->name = new Zend_Form_Element_Text('name');
        $this->name
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', false, array('max' => 45, 'encoding' => 'utf-8'));
        
        $this->description = new Zend_Form_Element_Text('description');
        $this->description
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', false, array('max' => 200, 'encoding' => 'utf-8'));
        
        $this->is_auto_approve = new Zend_Form_Element_Text('is_auto_approve');
        $this->is_auto_approve
            ->setRequired(true)
            ->addValidator('Boolean');
        
        $this->user_list = new Zend_Form_Element_Multiselect('user_list');
        $this->user_list
            ->setRegisterInArrayValidator(false)
            ->addValidator('Db_RecordExists', false, array('table' => 'user', 'field' => 'unique_id'));
        
        // strict for uploading image via service, if user does not select any image to upload
        // so the device will not send any data up to server. But the adapter will misunderstanding
        // that upload is failure and require a $_FILES data with error is 4 (mean not uploaded)
        // to fix this issue, we unset the image element if the device does not provide any upload data
        $imageConfig = Zend_Registry::get('app_config')->group->image;
        $this->image = App_Form_Factory::imageUpload($imageConfig);
        
        return $this;
    }
    
    /**
     * Do rollback action if form is failure to perform
     * 
     * @return Form_UserGroup
     */
    public function rollback()
    {
        if ($imagePath = $this->image->getFilename()) {
            @unlink($imagePath);
        }
        
        return $this;
    }
    
}