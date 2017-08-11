<?php

/**
 * Zend Form for search in page summary advertisement
 * 
 * @package Forms
 */
class Filter_SummaryAdvertisement extends Qsoft_Form_Abstract
{

    public function __construct($options = array())
    {
        parent::__construct(null, $options);
        
        $this->setAttrib('class', 'search-form mini');
        $this->setMethod('get');
        
        $this->advertisement_code = new Zend_Form_Element_Text('advertisement_code');
        $this->advertisement_code->setLabel('Advertisement Code')
            ->addFilter('StripTags')
            ->addFilter('StringTrim');
        
        $this->media_group = new Zend_Form_Element_Select('media_group');
        $this->media_group->setLabel('Media Group')
                          ->addMultiOption('', '')
                          ->addMultiOption(Dto_SummaryAdvertisement::MEDIA_GROUP_PAGE, 'Page')
                          ->addMultiOption(Dto_SummaryAdvertisement::MEDIA_GROUP_WEB, 'Web')
                          ->addMultiOption(Dto_SummaryAdvertisement::MEDIA_GROUP_AD,  'Ad');
        
        
        $this->date_hour = new Qsoft_Form_Element_DateRanger('date_hour');
        $this->date_hour->setLabel('Date');
        
        $this->submit = App_Form_Factory::doFilterButton();
        
        $this->setDefaultDecorators();
    }

}
