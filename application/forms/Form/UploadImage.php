<?php

/**
 * Form_UploadImage classs
 * 
 * Use for validate upload user image 
 * 
 * @package Form
 * @author sonvq
 */
class Form_UploadImage extends Qsoft_Form_Abstract
{

    /**
     * The class of DTO object
     * @var string
     */
    protected $_dtoClass = "Dto_User";

    /**
     * Contructor
     * 
     * @return Form_ApiRegister
     */
    public function __construct()
    {
        parent::__construct();

        $imageConfig = Zend_Registry::get('app_config')->user->image;
        $this->image = App_Form_Factory::imageUpload($imageConfig);
        
        return $this;
    }

}