<?php

/**
 * Dto_User
 * 
 * @package Dto
 * @author duyld
 */
class Dto_User extends Qsoft_Dto_Abstract
{

    /**
     * Name of the class of the Zend_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = 'Dao_User';

    const STATE_ACTIVE = 'active';
    const STATE_DELETE = 'delete';
    const STATE_BLOCK = 'block';
    const USER_ID_MAX_LENGHT = 20; // the characters length of user id column in database schema
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;
    const VALUATION_VERY_GOOD = 1;
    const VALUATION_GOOD = 2;
    const VALUATION_NORMAL = 3;
    const VALUATION_BAD = 4;
    const VALUATION_VERY_BAD = 5;
    const USER_ID_CHANGED = 1;
    const USER_ID_NOT_CHANGED = 0;
    
    /**
     * Initialize object
     *
     * Called from __construct() as final step of object instantiation.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        
        if (null === $this->point) {
            $this->point = 0;
        }
    }
    
    /**
     * Return the chat user name
     * 
     * @return string
     */
    public function getChatUsername()
    {
        return $this->unique_id;
    }
    
    /**
     * Return the chat password
     * 
     * @return string
     */
    public function getChatPassword()
    {
        return $this->unique_id;
    }
    
    /**
     * Check whether user is deleted or not
     * @return boolean
     */
    public function isDeleted()
    {
        return ($this->state == self::STATE_DELETE);
    }
    
    /**
     * Check the user id is changed after register
     * 
     * @return boolean
     */
    public function isChangedUserId()
    {
        return ($this->is_user_id_changed == self::USER_ID_CHANGED);
    }
    
    /**
     * Set user id is changed
     * 
     * @return Dto_User
     */
    public function setUserIdIsChanged()
    {
        $this->is_user_id_changed = self::USER_ID_CHANGED;
        return $this;
    }
    
    /**
     * Check whether user is allow to find by id or not
     */
    public function isAllowToFindById()
    {
        return (boolean) $this->is_allow_find_by_id;
    }
    
    /**
     * Return the array that contains the end user data
     * 
     * @return array
     */
    public function toEndUserArray()
    {
        $array = $this->toArray(array('unique_id', 'user_id', 'nick_name', 'avatar_id',
        	'emoticon_id', 'description', 'point', 'token',
            'call_number_id', 'call_password', 'call_domain', 'call_proxy',
            'longitude', 'latitude'));

        $array['small_image'] = $this->getSmallImageUrl();
        $array['large_image'] = $this->getLargeImageUrl();
        $array['hatochan_avatar'] = $this->getHatoChanAvatarUrl();
        
        $array['call_active'] = 'http://122.218.102.204';
        
        return $array;
    }
    
    /**
     * Return the array that contains the contact data
     *
     * @return array
     */
    
    public function toContactArray()
    {
    	$array = $this->toArray(array('unique_id', 'user_id', 'nick_name', 'call_number_id', 'description', 'avatar_id', 'emoticon_id'));
    	$array['small_image'] = $this->getSmallImageUrl();
        if (isset($this->distance)) {
            $array['distance'] = $this->distance;
        }
    
    	return $array;
    }

    /**
     * Return the array that contains user profile information
     * 
     * @return array
     */
    public function toProfileArray()
    {
        $arrayProfile = $this->toArray(array('nick_name', 'emoticon_id',
                'avatar_id', 'description', 'call_number_id'));
        $arrayProfile['small_image'] = $this->getSmallImageUrl();
        $arrayProfile['large_image'] = $this->getLargeImageUrl();
        
        return $arrayProfile;
    }
    
    /**
     * Return the array that contains user contact information for another user
     * 
     * @return array
     */
    public function toContactWithFriendStatusArray()
    {
        $array = $this->toContactArray();
        $array['is_friend'] = (int) $this->isFriendWithSub();
        
        return $array;
    }
    
    /**
     * Check whether user is friend with another user with the sub value contained
     * 
     * @return boolean
     */
    public function isFriendWithSub()
    {
        if ( ! isset($this->sub)) {
            throw new RuntimeException(
                'This user dto does not contain the sub value to check'
            );
        }
        
        return ($this->sub == Dto_Chat_Roster::SUBSCRIPTION_STATE_BOTH
            OR $this->sub == Dto_Chat_Roster::SUBSCRIPTION_STATE_TO);
    }
    
    /**
     * Return the array that contains user privacy settings only
     * 
     * @return array
     */
    public function toPrivacySettingsArray()
    {
        return $this->toArray(array('is_allow_call_by_search', 'is_allow_find_by_id',
            'is_allow_find_by_shake', 'is_allow_talk_by_search', 'is_passcode_enable', 'passcode'));
    }
    
    /**
     * Return the array that contains user notification settings only
     * 
     * @return array
     */
    public function toNotificationSettingsArray()
    {
        return $this->toArray(array('is_allow_notification', 'mute_notify',
            'tone_id', 'is_new_message_notify', 'is_show_preview', 'is_allow_group_invitation',
            'is_allow_in_app_alert', 'is_allow_in_app_sound',
            'is_allow_in_app_vibration', 'is_call_receive'));
    }

    /**
     * Return the array that contains only user images
     * 
     * @return array
     */
    public function toImageUrlArray()
    {
        $array['small_image'] = $this->getSmallImageUrl();
        $array['large_image'] = $this->getLargeImageUrl();
        return $array;
    }
    
    /**
     * Return the absolute url of hato chan image
     *
     * @return string
     */
    public function getHatoChanAvatarUrl()
    {
        if ( ! $this->hatochan) {
            return '';
        }
        
        $url = Zend_Registry::get('app_config')->user->hatochan->avatar->url;
        return Qsoft_Helper_Url::generate($url . '/' . $this->hatochan);
    }

    /**
     * Return the absolute url of large image
     * 
     * @return string
     */
    public function getLargeImageUrl()
    {
        return self::largeImageUrl(isset($this->user_img) ? $this->user_img : '');
    }

    /**
     * Return the absolute url of small image
     * 
     * @return string
     */
    public function getSmallImageUrl()
    {
        return self::smallImageUrl(isset($this->user_img) ? $this->user_img : '');
    }
    
    /**
     * Return the absolute url for small image
     * 
     * @return string
     */
    public static function smallImageUrl($image)
    {
        if ( ! $image) {
            return '';
        }
        
        $url = Zend_Registry::get('app_config')->user->image->small->url;
        return Qsoft_Helper_Url::generate($url . '/' . $image);
    }
    
    /**
     * Return the absolute url for large image
     * 
     * @return string
     */
    public static function largeImageUrl($image)
    {
        if ( ! $image) {
            return '';
        }
        
        $url = Zend_Registry::get('app_config')->user->image->large->url;
        return Qsoft_Helper_Url::generate($url . '/' . $image);
    }
    
    /**
     * Return file path of large image
     * 
     * @return string
     */
    public function getLargeImagePath()
    {
        $largePath = Zend_Registry::get('app_config')->user->image->large->uploadPath;
        return $this->_imagePath($largePath);
    }
    
    /**
     * Return file path of small image
     * 
     * @return string
     */
    public function getSmallImagePath()
    {
        $smallPath = Zend_Registry::get('app_config')->user->image->small->uploadPath;
        return $this->_imagePath($smallPath);
    }
    
    /**
     * Return the file path of image base on provided path
     * 
     * @param   string  $path
     * @return  string
     */
    protected function _imagePath($path)
    {
        if (empty($this->user_img)) {
            return '';
        }
        
        return Qsoft_Helper_File::getPath($path) . $this->user_img;
    }

}