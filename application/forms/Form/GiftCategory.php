<?php

/**
 * Zend Form for GiftCategory
 * 
 * @package Forms
 */
class Form_GiftCategory extends Qsoft_Form_Abstract
{

    /**
     * List of dto class for mapping between dto and form
     * 
     * @var array $_dtoClass
     */
    protected $_dtoClass = 'Dto_GiftCategory';

    public function __construct($data = null)
    {
        parent::__construct($data);
        
        $this->setAttrib('class', 'index-form');        
    
        //$clause = 'gift_category_id != ' . $data->gift_category_id;
        $this->gift_category_name = new Zend_Form_Element_Text('gift_category_name');
        $this->gift_category_name->setLabel('Gift Category Name')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('NotEmpty')
            ->addValidator('Db_NoRecordExists', false, array('table' => 'gift_category', 'field' => 'gift_category_name'));
        if(isset($data))
        {
            $clause = 'gift_category_id != ' . $data->gift_category_id;
            $this->gift_category_name->addValidator('Db_NoRecordExists', false, array('table' => 'gift_category', 'field' => 'gift_category_name', 'exclude' => $clause));    
        }    
        
        if ($this->getStage() == self::STAGE_EDIT) {
            
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
