<?php

/**
 * Zend Form for User
 *
 * @package Forms
 */
class Form_User extends Qsoft_Form_Abstract
{

    /**
     * List of dto class for mapping between dto and form
     *
     * @var array $_dtoClass
     */
    protected $_dtoClass = 'Dto_User';

    public function __construct($data = null)
    {
        parent::__construct($data);

        $this->id = new Zend_Form_Element_Text('id', array('readonly' => 'readonly'));
        $this->id->setLabel('ID');

        $this->user_id = new Zend_Form_Element_Text('user_id', array('readonly' => 'readonly'));
        $this->user_id->setLabel('User ID');

        $this->state = new Zend_Form_Element_Select('state');
        $this->state->setLabel('State')
            //->setRequired(true)
            ->addMultiOption(Dto_User::STATE_ACTIVE, 'Active')
            ->addMultiOption(Dto_User::STATE_DELETE, 'Delete')
            ->addMultiOption(Dto_User::STATE_BLOCK,  'Block');

        $this->nick_name = new Zend_Form_Element_Text('nick_name');
        $this->nick_name->setLabel('Nick name')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('NotEmpty');

        $this->avatar_id = new Zend_Form_Element_Radio('avatar_id', array('escape' => false));
        $this->avatar_id->setLabel('Avatar');

        $avatarDao = new Dao_UserAvatar();
        foreach ($avatarDao->fetchAll() as $avatar) {
            $url = Qsoft_Helper_Url::generate(Zend_Registry::get('app_config')->user->avatar->url.$avatar->avatar_img);
            $image = "<img width='30' src='$url' />";
            $this->avatar_id->addMultiOption($avatar->avatar_id, html_entity_decode($image));
        }
        $this->avatar_id->setSeparator(' ');

        //$this->pref = new Zend_Form_Element_Select('pref');
        //$this->pref->setLabel('Region')
        //           ->setRequired(true)
        //           ->addMultiOption('', '');
        //$regionDao = new Dao_Region();
        //foreach($regionDao->fetchAll() as $region)
        //{
        //    $this->pref->addMultiOption($region->id,$region->region_name);
        //}

        $this->black_list = new Zend_Form_Element_Text('black_list', array('readonly' => 'readonly'));
        $this->black_list->setLabel('Black List')
                         ->setAttrib('disabled','disabled');



        $this->created_at = new Zend_Form_Element_Text('created_at', array('readonly' => 'readonly'));
        $this->created_at->setLabel('Created at');

        $this->last_access = new Zend_Form_Element_Text('last_access', array('readonly' => 'readonly'));
        $this->last_access->setLabel('Last access');

        $this->valuation = new Zend_Form_Element_Select('valuation');
        $this->valuation->setLabel('Valuation')
            ->addMultiOption(Dto_User::VALUATION_VERY_GOOD, 'Very good')
            ->addMultiOption(Dto_User::VALUATION_GOOD, 'Good')
            ->addMultiOption(Dto_User::VALUATION_NORMAL,  'Normal')
            ->addMultiOption(Dto_User::VALUATION_BAD,  'Bad')
            ->addMultiOption(Dto_User::VALUATION_VERY_BAD,  'Very bad')
            ->setSeparator(' ');

        $this->submit = App_Form_Factory::submitButton(array('class' => 'confirm'));

        $this->setDefaultDecorators();

        if ($data instanceof Qsoft_Dto_Abstract) {
            $this->mapDtoToForm($data);
        }
    }

    /**
     * Convert all Dto's properties to form object
     *
     * @param DTO class $item
     */
    public function mapDtoToForm($item)
    {
        $itemClone = clone $item;
        if ($itemClone->black_list == 1) {
            $itemClone->black_list = 'Black list';
        } else {
            $itemClone->black_list = 'Not in Black list';
        }
        parent::mapDtoToForm($itemClone);
    }

}