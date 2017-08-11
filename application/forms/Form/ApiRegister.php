<?php

/**
 * Form_ApiRegister classs
 * 
 * Use for validate all request parameters required to register chat account in services
 * 
 * @package Form
 * @author duyld
 */
class Form_ApiRegister extends Qsoft_Form_Abstract
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


        $this->device_id = new Zend_Form_Element_Text('device_id');
        $this->device_id
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', false, array('max' => 32, 'min' => 6, 'encoding' => 'utf-8'))
            ->addValidator('Db_NoRecordExists', false, array(
                'table' => 'user', 'field' => 'unique_id', 'exclude' => 'state != "' . Dto_User::STATE_DELETE . '"'
            ))
        ;

        $this->user_id = new Zend_Form_Element_Text('user_id');
        $this->user_id
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(false)
            ->addValidator('StringLength', false, array('max' => Dto_User::USER_ID_MAX_LENGHT, 'min' => 6))
            ->addValidator('Db_NoRecordExists', false, array(
                'table' => 'user', 'field' => 'user_id', 'exclude' => 'state != "' . Dto_User::STATE_DELETE . '"'
            ))
            ->addValidator('Regex', false, array(
                    'pattern' => '/^[-a-zA-Z0-9]*$/u'))
        ;

        $this->nick_name = new Zend_Form_Element_Text('nick_name');
        $this->nick_name
            ->addFilter('StripTags')
            //->addFilter('PregReplace', array('match' => '/\s*/', 'replace' => ''))
            ->setRequired(false)
            ->addValidator('StringLength', false, array('max' => 20, 'encoding' => 'utf-8'))
            ->addValidator('Regex', false, array(
                'pattern' => '/^[-a-zA-Z0-9_\x{30A0}-\x{30FF}'
                . '\x{3040}-\x{309F}\x{4E00}-\x{9FAF}'
                . '\x{3400}-\x{4DBF}]*$/u'))
        ;

        $avatarDao = new Dao_UserAvatar();
        $this->avatar_id = new Zend_Form_Element_Text('avatar_id');
        $this->avatar_id
            ->addFilter('Int')
            ->setRequired(true)
            ->addValidator('NotEmpty')
            ->addValidator('Int')
            ->addValidator('InArray', false, array(
                'haystack' => $avatarDao->fetchAll()->getPrimaryKeys(),
            ))
        ;
        
        $emoticonDao = new Dao_UserEmoticon();
        $this->emoticon_id = new Zend_Form_Element_Text('emoticon_id');
        $this->emoticon_id
            ->setRequired(false)
            ->addValidator('NotEmpty')
            ->addValidator('Int')
            ->addValidator('InArray', false, array(
                'haystack' => $emoticonDao->fetchAll()->getPrimaryKeys(),
            ))
        ;

        $this->description = new Zend_Form_Element_Text('description');
        $this->description
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(false)
            ->addValidator('StringLength', false, array('max' => 200, 'encoding' => 'utf-8'));

        ;

        return $this;
    }

}