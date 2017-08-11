<?php

/**
 * Zend Form for Nickname
 * 
 * @package Forms
 */
class Form_Nickname extends Qsoft_Form_Abstract
{

    /**
     * List of dto class for mapping between dto and form
     * 
     * @var array $_dtoClass
     */
    protected $_dtoClass = 'Dto_NickName';

    public function __construct($data = null)
    {
        parent::__construct($data);
        
        $this->setAttrib('class', 'index-form');
        
        $this->nickname = new Zend_Form_Element_Text('nickname');
        $this->nickname
            ->setLabel('Nick name')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('StringLength', false, array('max' => 20, 'encoding' => 'utf-8'))
            ->addValidator('Regex', false, array(
                'pattern' => '/^[-a-zA-Z0-9_\x{30A0}-\x{30FF}'
                . '\x{3040}-\x{309F}\x{4E00}-\x{9FAF}'
                . '\x{3400}-\x{4DBF}]*$/u'))
            ->addValidator('Db_NoRecordExists', false, array('table' => 'nickname', 'field' => 'nickname'))
        ;
        if(isset($data))
        {
            $clause = 'id != ' . $data->id;
            $this->nickname->addValidator('Db_NoRecordExists', false, array('table' => 'nickname', 'field' => 'nickname', 'exclude' => $clause));    
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