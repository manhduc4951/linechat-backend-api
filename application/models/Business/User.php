<?php

/**
 * Business_User class
 *
 * @package LineChatApp
 * @subpackage Business
 * @author duyld
 */
class Business_User extends Business_Abstract
{
    /**
     * Call database adapter instance
     * 
     * @var Zend_Db_Adapter_Pdo_Abstract
     */
    protected $callDbAdapter;        

    /**
     * Nick name master DAO
     *
     * @var Dao_NickName
     */
    protected $nickNameDao;
    
    /**
     * User image DAO
     *
     * @var Dao_UserImage
     */
    protected $imageDao;
    
    /**
     * Public home setting Dao
     * 
     * @var Dao_UserPublicHomeSetting
     */
    protected $pubicHomeSettingDao;
    
    /**
     * Hide setting Dao
     *
     * @var Dao_UserHideSetting
     */
    protected $hideSettingDao;
    
    /**
     * Prefix of duplicate auto-generation user id
     */
    const USER_ID_PREFIX = '@';

    /**
     * Constructor
     *
     * @return Business_User
     */
    public function __construct()
    {
    	parent::__construct();
        $this->nickNameDao = new Dao_NickName();
        $this->imageDao = new Dao_UserImage();
        $this->pubicHomeSettingDao = new Dao_UserPublicHomeSetting();
        $this->hideSettingDao = new Dao_UserHideSetting();
//         $appConfig = Zend_Registry::get('app_config');
//         $adapter = new Zend_Db_Adapter_Pdo_Mysql($appConfig->resources->multidb->call);
//         var_dump($adapter->fetchAssoc('select * from subscriber'));
//         ini_set('memory_limit', '10000M');
//         set_time_limit(0);
//         foreach ($this->userDao->getAdapter()->fetchAssoc('select * from user where state=\'active\'') as $userDto) {
//             $userDto = new Dto_User($userDto);
//             $this->initializeCallSettings($userDto);
//             echo $userDto->unique_id . ' have call id: ' . $userDto->call_number_id . '<br/>'; 
//         }
//         die;

        return $this;
    }

    /**
     * Create new user instance
     *
     * @param   Dto_User    $userDto
     * @param   string      $deviceId
     * @return  array   Result array
     */
    public function create(Dto_User $userDto, $deviceId)
    {
        // start the transaction
        $this->userDao->getAdapter()->beginTransaction();

        $this
            // automatic pick one nick name from
            // database if user does not provide one
            ->generateNickName($userDto)
            // generate an unique id for user instance for authenticate
            ->generateUniqueId($userDto, $deviceId)
            // generate an unique user id if user does not provide one
            ->generateUserId($userDto)
        ;

        $userDto->state = Dto_User::STATE_ACTIVE;
        $userDto->hatochan = 'hatochan.png';

        // do insert a new record
        $this->userDao->insert($userDto);

        // create new account in chat server
        $client = XmppFactory::create();
        $response = $client->register($userDto->getChatUsername(), $userDto->getChatPassword(), $userDto->nick_name);
        
        if ($response->isFailure()) {
            $this->userDao->getAdapter()->rollBack();
            return $this->_readXmppResponse($response);
        }
        
        // commit all completed queries
        $this->userDao->getAdapter()->commit();
        
        $this->initializeCallSettings($userDto);
        
        // auto login
        $this->apiLogin($userDto);
        
        return array('status' => true);
    }
    
    /**
     * inilialize new call settings and save automatically for user
     * 
     * @param Dto_User $userDto
     * @return void
     */
    public function initializeCallSettings(Dto_User $userDto)
    {
        $hash = Qsoft_Helper_Security::FNVHash($userDto->id);
        
        // SIP configuration
        $appConfig = Zend_Registry::get('app_config');
        $userDto->call_domain = $appConfig->call->domain;
        $userDto->call_number_id = $hash;
        $userDto->call_password = $hash;
        
        // detect call proxy
        $adapter = $this->getCallDbAdapter();
        $proxies = $adapter->fetchAll("select * from qsoft_rt_table order by internal", array(), Zend_Db::FETCH_ASSOC);
        $totalProxies = count($proxies);
        $index = abs($userDto->call_number_id % $totalProxies);
        
        $userDto->call_proxy = $proxies[$index]['external'];
        
        $this->userDao->update($userDto, array('call_domain', 'call_proxy', 'call_number_id', 'call_password'));
        
        // register call account
        return $this->registerCallAccount($userDto);
    }
    
    /**
     * Retrieve call database adapter instance
     * 
     * @return Zend_Db_Adapter_Pdo_Abstract
     */
    protected function getCallDbAdapter()
    {
        if (null == $this->callDbAdapter) {
            $appConfig = Zend_Registry::get('app_config');
            $this->callDbAdapter = new Zend_Db_Adapter_Pdo_Mysql($appConfig->resources->multidb->call);
        }
        
        return $this->callDbAdapter;
    }
    
    /**
     * Register a call account
     * 
     * @param Dto_User $userDto
     * @return void
     */
    public function registerCallAccount(Dto_User $userDto)
    {
        $username = $userDto->call_number_id;
        $password = $userDto->call_password;
        $domain = $userDto->call_domain; 
        
        $ha1 = md5($username.':'.$domain.':'.$password);
        $ha1b = md5($username.'@'.$domain.':'.$domain.':'.$password);
        
//        $request = compact('username', 'domain', 'password', 'ha1', 'ha1b');
//        return 'http://122.218.102.204/call_setup.php?' . http_build_query($request);

         $adapter = $this->getCallDbAdapter();
         if ( ! $adapter->fetchRow("select * from subscriber where username = '$username'")) {
             $adapter->insert('subscriber', array(
                 'username' => $username,
                 'domain' => $domain,
                 'password' => $password,
                 'ha1' => $ha1,
                 'ha1b' => $ha1b
             ));
         }
    }

    /**
     * Set an user instance to deleted state
     *
     * @param Dto_User $userDto
     * @return array
     */
    public function delete(Dto_User $userDto)
    {
        if ($userDto->isDeleted()) {
            return array('status' => false, 'error_code' => ERROR_USER_ALREADY_DELETED);
        }
        
        // start transaction
        //$this->userDao->getAdapter()->beginTransaction();
        
        // send message to user
        $messageBusiness = new Business_Message();
        $messageBusiness->sendMessage($userDto, 'delete_warning');
        
        // delete user in database
        $userDto->state = Dto_User::STATE_DELETE;
        $this->userDao->update($userDto, 'state');
        
        // delete all joined room
        $chatRoomDao = new Dao_ChatRoomUser();
        $chatRoomDao->deleteBy('unique_id', $userDto->unique_id);
        
        // delete all groups that this user is owner
        $groupBusiness = new Business_UserGroup();
        $groups = $groupBusiness->getCreatedNodes($userDto);
        foreach ($groups as $groupDto) {
            $groupBusiness->delete($groupDto, $userDto);
        }
        
        // leave all group that user subscribed
        $groups = $groupBusiness->getSubscribedNodes($userDto);
        foreach ($groups as $groupDto) {
            $groupBusiness->unsubscribe($groupDto, $userDto);
        }
        
        // delete user from xmpp
        $client = XmppFactory::create($userDto);
        $response = $client->delete();
        
        // delete call database
//         $appConfig = Zend_Registry::get('app_config');
//         $callAdapter = new Zend_Db_Adapter_Pdo_Mysql($appConfig->resources->multidb->call);
//         $callAdapter->delete('subscriber', "username = '$userDto->call_number_id'");
        
        //if ($response->isFailure()) {
        //    $this->userDao->getAdapter()->rollBack();
        //    return $this->_readXmppResponse($response);
        //}
        
        //$this->userDao->getAdapter()->commit();
        
        return array('status' => true);
    }

    /**
     * Generate a change device code for user instance
     *
     * @param   Dto_User    $userDto
     * @return  array   Result array
     */
    public function generateChangeDeviceCode(Dto_User $userDto)
    {
        $expiredTime = Zend_Registry::get('api_config')->user->changeDevice->expiredTime;

        $userDto->change_device_code = Qsoft_Helper_String::random('ALNUM', 5);
        $userDto->change_device_expired_at = Qsoft_Helper_Datetime::time(time() + $expiredTime);

        $this->userDao->update($userDto, array('change_device_code', 'change_device_expired_at'));

        return array('status' => true);
    }

    /**
     * Move user data to a new device
     *
     * @param   Dto_User    $userDto
     * @param   string      $deviceId   The device id to change to
     * @return  array   Result array
     */
    public function changeDevice($userDto, $deviceId)
    {
        // the change device code must be match with an existing record
        if ( ! $userDto) {
            return array('status' => false, 'error_code' => ERROR_CHANGE_DEVICE_CODE_INVALID);
        }

        // the change device code must not be expired
//         if (Qsoft_Helper_Datetime::getRange(time(), $userDto->change_device_expired_at) < 0) {
//             return array('status' => false, 'error_code' => ERROR_CHANGE_DEVICE_CODE_EXPIRED);
//         }

        // every thing alright, allow user to change
        // begin change progress
        $this->userDao->getAdapter()->beginTransaction();
        
        if ($userDtoWantToChange = $this->userDao->fetchOnePublicBy('unique_id', $deviceId)) {
            $delete = $this->delete($userDtoWantToChange);
            if (true !== $delete['status']) {
                $this->userDao->getAdapter()->rollBack();
                return $delete;
            }
        }
        
        $oldUniqueId = $userDto->unique_id;
        
        // update backend database
        $this->generateUniqueId($userDto, $deviceId);
//         $userDto->change_device_code = null;
//         $userDto->change_device_expired_at = null;

//         $this->userDao->update($userDto, array('unique_id', 'change_device_code', 'change_device_expired_at'));
        $this->userDao->update($userDto, array('unique_id'));
        
        // register new account
        $client = XmppFactory::create();
        $response = $client->register($userDto->getChatUsername(), $userDto->getChatPassword(), $userDto->nick_name);
        
        if ($response->isFailure()) {
            $this->userDao->getAdapter()->rollBack();
            return $this->_readXmppResponse($response);
        }
        
        // also change all related data for chat database from old device to the new one
        $this->changeUsernameForChatDatabase($oldUniqueId, $userDto->unique_id);
        
        // complete
        $this->userDao->getAdapter()->commit();
        return array('status' => true);
    }
    
    /**
     * Change the username for all table data of chat database to new unique id
     * 
     * @param string $oldUniqueId
     * @param string $newUniqueId
     * @return Business_User
     */
    public function changeUsernameForChatDatabase($oldUniqueId, $newUniqueId)
    {
        // all tables and columns need to make change
        $changeMap = array(
            'ofConParticipant' => array(
                'columns' => 'bareJID'
            ),
            'ofMessageArchive' => array(
                'columns' => array('fromJID', 'toJid')
            ),
            'ofMucAffiliation' => array(
                'columns' => 'jid'
            ),
            'ofOffline' => array(
                'columns' => array('username' => array('node_only' => true))
            ),
            'ofPresence' => array(
                'columns' => array('username' => array('node_only' => true))
            ),
            'ofPrivacyList' => array(
                'columns' => array(
                    'username' => array('node_only' => true),
                    'name',
                    'list'
                )
            ),
            'ofPubsubAffiliation' => array(
                'columns' => 'jid'
            ),
            'ofPubsubItem' => array(
                'columns' => 'jid'
            ),
            'ofPubsubNode' => array(
                'columns' => 'creator'
            ),
            'ofPubsubSubscription' => array(
                'columns' => array('jid', 'owner')
            ),
            'ofRoster' => array(
                'columns' => array(
                    'username' => array('node_only' => true),
                    'jid'
                )
            ),
            'ofVCard' => array(
                'columns' => array('username' => array('node_only' => true))
            ),
            'chat_room_user' => array(
                'columns' => array('unique_id' => array('node_only' => true))
            ),
        );
        
        $adapter = $this->userDao->getAdapter();
        $newBareJid = XmppFactory::createBareJid($newUniqueId);
        $oldBareJid = XmppFactory::createBareJid($oldUniqueId);
        
        $adapter->query("DELETE FROM ofPubsubAffiliation WHERE jid = '$newBareJid'");
        $adapter->query("DELETE FROM ofPrivacyList WHERE username = '$newUniqueId'");
        $adapter->query("DELETE FROM ofPubsubSubscription WHERE jid = '$newBareJid'");
        $adapter->query("DELETE FROM ofPubsubSubscription WHERE owner = '$newBareJid'");
        $adapter->query("DELETE FROM ofUser WHERE username = '$oldUniqueId'");
        
        foreach ($changeMap as $table => $data) {
            if ( ! is_array($data['columns'])) {
                $data['columns'] = array($data['columns']);
            }
            
            foreach ($data['columns'] as $index => $column) {
                if ( ! is_int($index)) {
                    $options = $column;
                    $column = $index;
                } else {
                    $options = array();
                }
                
                $nodeOnly = (isset($options['node_only']) AND true === $options['node_only']);
                $query = "UPDATE {$table} ";
                $query .= ' SET ' . $column . " = REPLACE({$column}, '".
                    ($nodeOnly ? $oldUniqueId : $oldBareJid)."', '".
                    ($nodeOnly ? $newUniqueId : $newBareJid)."')";
                
//                if ($table == 'ofPrivacyList' and $column == 'list') {
//                    $query .= " WHERE {$column} LIKE '%{$oldBareJid}%'";
//                } else {
//                    $query .= " WHERE {$column} LIKE '" . ($nodeOnly ? $oldUniqueId . "'" : $oldBareJid . "%'");
//                }
                
                $adapter->query($query);
            }
        }
        
        return $this;
    }

    /**
     * Do login for api session and return the token string
     *
     * @param   Dto_User    $userDto
     * @param   float       $longitude
     * @param   float       $latitude
     * @return  array       Result array
     */
    public function apiLogin($userDto, $longitude = null, $latitude = null)
    {
        $userDto->token = $this->generateToken();
        $userDto->last_access = Qsoft_Helper_Datetime::current();
        $fields = array('token', 'last_access');
        
        // update the coordinate of user
        if (strlen($longitude) AND strlen($latitude)) {
            $userDto->longitude = $longitude;
            $userDto->latitude = $latitude;
            $fields[] = 'longitude';
            $fields[] = 'latitude';
        }
        $this->userDao->update($userDto, $fields);
        
        Business_Log::getInstance()->appStartLog($userDto);

        return array('status' => true);
    }

    /**
     * Generate an unique id for an user instance from device id
     *
     * @param   Dto_User    $userDto
     * @param   string      $deviceId
     * @return  Business_User
     */
    public function generateUniqueId($userDto, $deviceId)
    {
        $userDto->unique_id = $deviceId;

        return $this;
    }

    /**
     * Automatic pick one nick name from database if user dto does
     * not contains a valid one
     *
     * @param   Dto_User    $userDto
     * @return  Business_User
     */
    public function generateNickName(Dto_User $userDto)
    {
        if ( ! is_string($userDto->nick_name) OR strlen($userDto->nick_name) < 1) {
            $userDto->nick_name = $this->nickNameDao->fetchRandom()->nickname;
        }

        return $this;
    }

    /**
     * Generate an unique user id if user does not provide one
     *
     * @param   Dto_User    $userDto
     * @param   string      $prefix     The prefix to add to user id
     * @return  Business_User
     */
    public function generateUserId(Dto_User $userDto, $prefix = self::USER_ID_PREFIX)
    {
        if ( ! is_string($userDto->user_id) OR strlen($userDto->user_id) < 1) {
            // make sure the unique id has exist
            if (empty($userDto->unique_id)) {
                throw new RuntimeException('The unique id must be generete before perform this action');
            }

            $userDto->user_id = $prefix . substr($userDto->unique_id, 0, 6);

            // make sure the generated user id is an unique value
            while ($this->userDao->fetchRow(array('user_id = ?' => $userDto->user_id))) {
            	// if the characters length of user id is reached the limit after some loop,
            	// reset the string and continue looping
            	if (strlen($userDto->user_id) == Dto_User::USER_ID_MAX_LENGHT) {
            		$userDto->user_id = $prefix . substr($userDto->unique_id, 0, 6);
            	}

                $userDto->user_id .= rand(0, 9);
            }
        }
    }

    /**
     * Generate an unique token for a service login session
     *
     * @return string
     */
    protected function generateToken()
    {
        return base_convert(sha1(uniqid(microtime(), true)), 16, 36);
    }
    
    /**
     * Check whether user is blocked by another user or not
     * 
     * @param Dto_User $userDto
     * @param Dto_User $targetUserDto
     */
    public function isBlockedBy(Dto_User $userDto, Dto_User $targetUserDto)
    {
        // get the privacy instance of target user
        $privacyListDao = Dao_Chat_Factory::create('PrivacyList'); /* @var $privacyListDao Dao_Chat_PrivacyList */
        $privacyDto = $privacyListDao->fetchOneBy('username', $targetUserDto->getChatUsername());
        
        // target user does not have any privacy data
        if ( ! $privacyDto) return false;
        
        // read the privacy list
        $xml = new SimpleXMLElement($privacyDto->list);
        $xml->registerXPathNamespace('listpath', Xmpp::PRIVACY_NAMESPACE);
        
        // loop through all privacy items
        foreach ($xml->xpath('//listpath:item') as $item) {
            $username = $userDto->getChatUsername();
            $attributes = $item->attributes();
            $itemUsername = current(explode('@', $attributes['value']));
            
            if (strtolower($username) == strtolower($itemUsername)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Update user profile information
     *
     * @param   Dto_User        $userDto
     * @param   string          $fieldName
     * @param   string          $fieldValue
     * @return  array   Result array
     */
    public function update(Dto_User $userDto, $fieldName, $fieldValue = '')
    {
        $updatedFields = array($fieldName);
        $userDto->{$fieldName} = $fieldValue;
        
        // user can change the user id once
        if ($fieldName == 'user_id') {
            if ($userDto->isChangedUserId()) {
                return array('status' => false, 'error_code' => ERROR_USER_ID_CHANGE_TWICE);
            }
            
            $userDto->setUserIdIsChanged();
            $updatedFields[] = 'is_user_id_changed';
        }
        
        $this->userDao->update($userDto, $updatedFields);

        return array('status' => true);
    }
    
    /**
     * Update the image of user
     * 
     * @param Dto_User $userDto
     * @param string $fileName
     * @return array
     */
    public function updateImage(Dto_User $userDto, $fileName)
    {
        $image = new Dto_UserImage();
        $this->userDao->getAdapter()->beginTransaction();
        
        // create new image instance
        if ( ! empty($fileName)) {
            $image->user_id = $userDto->id;
            $image->user_img = $fileName;
            $image->created_at = Qsoft_Helper_Datetime::current();
            
            $this->imageDao->insert($image);
        }
        
        // set reference to user table
        // if file name is empty so image is an empty Dto and the is will be set to NULL
        $userDto->profile_user_img_id = $image->user_img_id;
        $this->userDao->update($userDto, 'profile_user_img_id');
        
        // set reference to image status table
        if ($image->user_img_id) {
            $imageBusiness = new Business_Image();
            $imageBusiness->addUserProfileImage($userDto, $image);
        }
        
        $this->userDao->getAdapter()->commit();
        $userDto->addColumn('user_img', $image->user_img);
        return array('status' => true);
    }
    
    /**
     * Delete current user image
     * 
     * @param Dto_User $userDto
     * @return array
     */
    public function deleteImage(Dto_User $userDto)
    {
        $userDto->profile_user_img_id = null;
        $this->userDao->update($userDto, 'profile_user_img_id');
        
        return array('status' => true);
    }
    
    /**
     * Retrieve all friends of user
     * 
     * @param Dto_User $userDto
     * @return Qsoft_Db_Table_Rowset of Dto_User
     */
    public function getFriendList(Dto_User $userDto)
    {
        // retrieve list of friends from chat database
        $rosterDao = Dao_Chat_Factory::create('Roster');
        $rosters = $rosterDao->fetchAllBy(array(
            'username' => $userDto->getChatUsername(),
            'sub' => array(Dto_Chat_Roster::SUBSCRIPTION_STATE_BOTH, Dto_Chat_Roster::SUBSCRIPTION_STATE_TO)
        ));
        
        $friendUniqueIds = array();
        foreach ($rosters as $roster) {
            Zend_Loader_Autoloader::autoload('Xmpp');
            $jid = new XMPPJid($roster->jid);
            $friendUniqueIds[$jid->node] = $jid->node;
        }
         
        // query for friends
        return $this->userDao->fetchPublicBy('unique_id', $friendUniqueIds);
    }
    
    /**
     * Retrieve public home settings
     * 
     * @param Dto_User $userDto
     * @return Qsoft_Db_Table_Rowset or Dto_User
     */
    public function getPublicHomeSetting(Dto_User $userDto)
    {
        $settings = $this->pubicHomeSettingDao->fetchAllBy('user_id', $userDto->id);
        $ids = array();
        foreach ($settings as $settingDto) {
            $ids[] = $settingDto->blocked_user_id;
        }
        $users = $this->userDao->fetchPublicBy('id', $ids);
        
        return $users;
    }
    
    /**
     * Update the public home settings of user
     * Block all provided users to view home
     * 
     * @param Dto_User $userDto
     * @param array $users
     * @return array
     */
    public function updatePublicHomeSetting(Dto_User $userDto, array $users = array())
    {
        $this->pubicHomeSettingDao->getAdapter()->beginTransaction();
        
        // delete current settings
        $this->pubicHomeSettingDao->deleteBy('user_id', $userDto->id);
        
        // add settings back
        foreach ($users as $blockedUserDto) {
            $setting = new Dto_UserPublicHomeSetting();
            $setting->user_id = $userDto->id;
            $setting->blocked_user_id = $blockedUserDto->id;
            
            $this->pubicHomeSettingDao->insert($setting);
        }
        
        $this->pubicHomeSettingDao->getAdapter()->commit();
        return array('status' => true);
    }
    
    /**
     * Retrieve user hide settings
     *
     * @param Dto_User $userDto
     * @return Qsoft_Db_Table_Rowset of Dto_User
     */
    public function getHideSetting(Dto_User $userDto)
    {
        $settings = $this->hideSettingDao->fetchAllBy('user_id', $userDto->id);
        $ids = array();
        foreach ($settings as $settingDto) {
            $ids[] = $settingDto->hide_user_id;
        }
        $users = $this->userDao->fetchPublicBy('id', $ids);
    
        return $users;
    }
    
    /**
     * Update the hide settings of user
     *
     * @param Dto_User $userDto
     * @param array $users
     * @return array
     */
    public function updateHideSetting(Dto_User $userDto, array $users = array())
    {
        $this->hideSettingDao->getAdapter()->beginTransaction();
    
        // delete current settings
        $this->hideSettingDao->deleteBy('user_id', $userDto->id);
    
        // add settings back
        foreach ($users as $hideUserDto) {
            $setting = new Dto_UserHideSetting();
            $setting->user_id = $userDto->id;
            $setting->hide_user_id = $hideUserDto->id;
    
            $this->hideSettingDao->insert($setting);
        }
    
        $this->hideSettingDao->getAdapter()->commit();
        return array('status' => true);
    }

}