<?php

/**
 * Zend Form for search User
 *
 * @package Forms
 */
class Filter_User extends Qsoft_Form_Abstract
{

    public function __construct($options = array())
    {
        parent::__construct(null,$options);

        $this->setAttrib('class', 'search-form');
        $this->setMethod('get');

        $this->id = new Zend_Form_Element_Text('id');
        $this->id->setLabel('ID')
            ->addFilter('StripTags')
            ->addFilter('StringTrim');

        $this->unique_id = new Zend_Form_Element_Text('unique_id');
        $this->unique_id->setLabel('Unique ID')
            ->addFilter('StripTags')
            ->addFilter('StringTrim');

        $this->user_id = new Zend_Form_Element_Text('user_id');
        $this->user_id->setLabel('User ID')
            ->addFilter('StripTags')
            ->addFilter('StringTrim');

        $this->nick_name = new Zend_Form_Element_Text('nick_name');
        $this->nick_name->setLabel('Nick name')
            ->addFilter('StripTags')
            ->addFilter('StringTrim');

        $this->avatar_id = new Zend_Form_Element_MultiCheckbox('avatar_id', array('escape' => false, 'disableLoadDefaultDecorators' => true));
        $this->avatar_id->setLabel('Avatar');

        $avatarDao = new Dao_UserAvatar();
        foreach ($avatarDao->fetchAll() as $avatar) {
            $url = Qsoft_Helper_Url::generate(Zend_Registry::get('app_config')->user->avatar->url.$avatar->avatar_img);
            $image = "<img width='30' src='$url' />";
            $this->avatar_id->addMultiOption($avatar->avatar_id, html_entity_decode($image));
        }
        $this->avatar_id->setSeparator(' ');



        $this->emoticon_id = new Zend_Form_Element_MultiCheckbox('emoticon_id', array('escape' => false, 'disableLoadDefaultDecorators' => true));
        $this->emoticon_id->setLabel('Emoticon');

        $emoticonDao = new Dao_UserEmoticon();
        foreach ($emoticonDao->fetchAll() as $emoticon) {
            $url = Qsoft_Helper_Url::generate(Zend_Registry::get('app_config')->user->emoticon->url.$emoticon->feeling_img);
            $image = "<img width='30' src='$url' />";
            $this->emoticon_id->addMultiOption($emoticon->feeling_img_id, html_entity_decode($image));
        }
        $this->emoticon_id->setSeparator(' ');


        //$this->pref = new Zend_Form_Element_Select('pref');
        //$this->pref->setLabel('Region')
        //           ->addMultiOption('', '');
        //$regionDao = new Dao_Region();
        //foreach($regionDao->fetchAll() as $region)
        //{
        //    $this->pref->addMultiOption($region->id,$region->region_name);
        //}

        //$this->age = new Zend_Form_Element_Select('age');
        //$this->age->setLabel('Age')
        //          ->addMultiOption('', '');
        //$userAgeDao = new Dao_UserAge();
        //foreach($userAgeDao->fetchAll() as $age)
        //{
        //    $this->age->addMultiOption($age->id,$age->age);
        //}

        $this->black_list = new Zend_Form_Element_Checkbox('black_list', array('disableHidden' => false));
        $this->black_list->setLabel('Blacklist')
                         ->setCheckedValue(1)
                         ->setUncheckedValue(0);

        $this->created_at = new Qsoft_Form_Element_DateRanger('created_at');
        $this->created_at->setLabel('Registration date');

        $this->last_access = new Qsoft_Form_Element_DateRanger('last_access');
        $this->last_access->setLabel('Last access');

        $this->state = new Zend_Form_Element_MultiCheckbox('state', array('disableLoadDefaultDecorators' => true));
        $this->state->setLabel('State')
            ->addMultiOption(Dto_User::STATE_ACTIVE, 'Active')
            ->addMultiOption(Dto_User::STATE_DELETE, 'Delete')
            ->addMultiOption(Dto_User::STATE_BLOCK,  'Block')
            ->setSeparator(' ');

        $this->valuation = new Zend_Form_Element_MultiCheckbox('valuation', array('disableLoadDefaultDecorators' => true));
        $this->valuation->setLabel('Valuation')
            ->addMultiOption(Dto_User::VALUATION_VERY_GOOD, 'Very good')
            ->addMultiOption(Dto_User::VALUATION_GOOD, 'Good')
            ->addMultiOption(Dto_User::VALUATION_NORMAL,  'Normal')
            ->addMultiOption(Dto_User::VALUATION_BAD,  'Bad')
            ->addMultiOption(Dto_User::VALUATION_VERY_BAD,  'Very bad')
            ->setSeparator(' ');

        $this->image_display = new Zend_Form_Element_MultiCheckbox('image_display', array('disableLoadDefaultDecorators' => true));
        $this->image_display->setLabel('Image Display')
            ->addMultiOption(1, 'Yes')
            ->addMultiOption(0, 'No')
            ->setSeparator(' ');

        $this->submit = App_Form_Factory::doFilterButton();

        $this->setTwoColumnDecorators();
    }

}
