<?php

/**
 * Zend Form for WordNotGood
 * 
 * @package Forms
 */
class Form_WordNotGood extends Qsoft_Form_Abstract
{

    /**
     * List of dto class for mapping between dto and form
     * 
     * @var array $_dtoClass
     */
    protected $_dtoClass = 'Dto_WordNotGood';

    public function __construct($data = null)
    {
        parent::__construct($data);
        
        $this->setAttrib('class', 'index-form');
        
        $this->word = new Zend_Form_Element_Text('word');
        $this->word->setLabel('Word')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('NotEmpty')
            ->addValidator('Db_NoRecordExists', false, array('table' => 'ng_word', 'field' => 'word'));
        if(isset($data))
        {
            $clause = 'id != ' . $data->id;
            $this->word->addValidator('Db_NoRecordExists', false, array('table' => 'ng_word', 'field' => 'word', 'exclude' => $clause));    
        }    
        
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
