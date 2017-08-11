<?php

/**
 * Zend Form for search Message History
 * 
 * @package Forms
 */
class Filter_MessageHistory extends Qsoft_Form_Abstract
{

    public function __construct($options = array())
    {
        parent::__construct(null, $options);
        
        $this->setAttrib('class', 'search-form');
        $this->setMethod('get');
        
        $this->body = new Zend_Form_Element_Text('body');
        $this->body->setLabel('Message')
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
        
        $this->group_name = new Zend_Form_Element_Text('group_name');
        $this->group_name->setLabel('Group')
            ->addFilter('StripTags')
            ->addFilter('StringTrim');
        
        $this->sentDate = new Qsoft_Form_Element_DateRanger('sentDate');
        $this->sentDate->setLabel('Sent Date');    
        
        $this->pref = new Zend_Form_Element_Select('pref');
        $this->pref->setLabel('Region')
                   ->addMultiOption('', '');
        $regionDao = new Dao_Region();
        foreach($regionDao->fetchAll() as $region)
        {
            $this->pref->addMultiOption($region->id,$region->region_name);
        }

        $this->age = new Zend_Form_Element_Select('age');
        $this->age->setLabel('Age')
                  ->addMultiOption('', '');
        $userAgeDao = new Dao_UserAge();
        foreach($userAgeDao->fetchAll() as $age)
        {
            $this->age->addMultiOption($age->id,$age->age);
        }
        
        $this->valuation = new Zend_Form_Element_MultiCheckbox('valuation', array('disableLoadDefaultDecorators' => true));
        $this->valuation->setLabel('Valuation')
            ->addMultiOption(Dto_User::VALUATION_VERY_GOOD, 'Very good')
            ->addMultiOption(Dto_User::VALUATION_GOOD, 'Good')
            ->addMultiOption(Dto_User::VALUATION_NORMAL,  'Normal')
            ->addMultiOption(Dto_User::VALUATION_BAD,  'Bad')
            ->addMultiOption(Dto_User::VALUATION_VERY_BAD,  'Very bad')            
            ->setSeparator(' ');
        
        $this->avatar_id = new Zend_Form_Element_MultiCheckbox('avatar_id', array('escape' => false, 'disableLoadDefaultDecorators' => true));
        $this->avatar_id->setLabel('Avatar');
        $avatarDao = new Dao_UserAvatar();
        foreach ($avatarDao->fetchAll() as $avatar) {          
            $url = Qsoft_Helper_Url::generate(Zend_Registry::get('app_config')->user->avatar->url.$avatar->avatar_img);                       
            $image = "<img width='30' src='$url' />";
            $this->avatar_id->addMultiOption($avatar->avatar_id, html_entity_decode($image));
        }
        $this->avatar_id->setSeparator(' ');
        
        $this->submit = App_Form_Factory::doFilterButton();
        
        $this->setTwoColumnDecorators();
    }

}
