<?php

/**
 * Zend Form for Pre Group
 * 
 * @package Forms
 */
class Form_Pregroup extends Qsoft_Form_Abstract
{

    /**
     * List of dto class for mapping between dto and form
     * 
     * @var array $_dtoClass
     */
    protected $_dtoClass = 'Dto_Pregroup';

    public function __construct($data = null)
    {
        parent::__construct($data);
        
        $this->setAttrib('class', 'index-form');
        
        $this->pre_group_name = new Zend_Form_Element_Text('pre_group_name');
        $this->pre_group_name
            ->setLabel('Group name')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('NotEmpty');
        
        $this->pref = new Zend_Form_Element_Select('pref');
        $this->pref->setLabel('Region')
            ->setRequired(true)
            ->addValidator('NotEmpty');
        $regionDao = new Dao_Region();
        foreach($regionDao->fetchAll() as $region)
        {
            $this->pref->addMultiOption($region->id,$region->region_name);
        }    
        
        $this->age = new Zend_Form_Element_Select('age');
        $this->age->setLabel('Age')
            ->setRequired(true)
            ->addValidator('NotEmpty');
        $userAgeDao = new Dao_UserAge();
        foreach($userAgeDao->fetchAll() as $age)
        {
            $this->age->addMultiOption($age->id,$age->age);
        }
        
        $this->show_hide = new Zend_Form_Element_Checkbox('show_hide', array('disableHidden' => true));
        $this->show_hide->setLabel('Reflect')
                        ->setCheckedValue(1)
                        ->setUncheckedValue(0);
        
        if ($this->getStage() == self::STAGE_EDIT) {
            $this->back = App_Form_Factory::backButton();
        }
        
        $this->submit = App_Form_Factory::submitButton(array('class' => 'confirm'));
        
        $this->setDefaultDecorators();
        
        if ($data instanceof Qsoft_Dto_Abstract) {
            $this->mapDtoToForm($data);
        }   
    }

}