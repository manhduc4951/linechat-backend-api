<?php

/**
 * Business_Message class
 *
 * @package LineChatApp
 * @subpackage Business 
 */
class Business_Shake extends Business_Abstract
{
    /**
     * Shake DAO
     *
     * @return Dao_Shake
     */
    protected $shakeDao;
    
    /**
     * The constructor
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->shakeDao = new Dao_Shake();
    }
    
    /**
     * Save user data to start look up
     * 
     * @param Dto_User $userDto
     * @param float $longitude
     * @param float $latitude
     * @return array
     */
    public function startLookUp(Dto_User $userDto, $longitude, $latitude)
    {
    	// TODO: remove hardcode
    	$keepTime = 15;     // seconds
    	
    	// create a newly shake instance
    	$this->createShakeDto($userDto, $longitude, $latitude, $keepTime);
        
        // update coordinate of user
        $userDto->longitude = $longitude;
        $userDto->latitude = $latitude;
        $this->userDao->update($userDto, array('longitude', 'latitude'));
        
    	return array('status' => true);
    }
    
    /**
     * Finding friends by shaking device
     * 
     * @param Dto_User $userDto
     * @param float $longitude
     * @param float $latitude
     * @return array
     */
    public function lookUp(Dto_User $userDto)
    {
        // TODO: remove hardcode
        $keepTime = 15;     // seconds
        $limitDistance = 0.5; // kilometers
        
        // get current shake instance
        // if cannot found, return the empty user rowset
        $shakeDto = $this->getShakeDto($userDto);
        if ( ! $shakeDto) {
        	return array('status' => true, 'users' => $this->userDao->find(-1));
        }
        
        $rowset = $this->_doLookUp($shakeDto, $userDto, $limitDistance);
        if ($rowset->count()) {
            // we found the users
            // mark the shake instance as completed, also change the completed time from limit to current
            $shakeDto->completed_at = Qsoft_Helper_Datetime::current();
            $this->shakeDao->update($shakeDto, 'completed_at');
        }
        
        $shakeSubArray = array();
        foreach ($rowset as $shakeDto) {
            $shakeSubArray[$shakeDto->user_id] = $shakeDto->sub;
        }
        
        // return result
        $users = $this->userDao->fetchAllBy('id', array_keys($shakeSubArray));
        
        foreach ($users as $userDto) {
            $userDto->addColumn('sub', $shakeSubArray[$userDto->id]);
        }
        
        return array('status' => true, 'users' => $users);
    }
    
    /**
     * Create a shake dto instance, or update the current instance if already exist
     * 
     * @param Dto_User $userDto
     * @param float $longitude
     * @param float $latitude
     * @param integer $keepTime
     * @return Dto_Shake
     */
    protected function createShakeDto(Dto_User $userDto, $longitude, $latitude, $keepTime)
    {
        if ( ! $shakeDto = $this->shakeDao->fetchOneBy('user_id', $userDto->id)) {
            $shakeDto = new Dto_Shake();
        }
        
        // insert shake data for anyone can look up
        $shakeDto->user_id = $userDto->id;
        $shakeDto->jid = XmppFactory::createBareJid($userDto->getChatUsername());
        $shakeDto->longitude = $longitude;
        $shakeDto->latitude = $latitude;
        $shakeDto->created_at = Qsoft_Helper_Datetime::current();
        // we increase the completed time to avoid app delay time
        $shakeDto->completed_at = Qsoft_Helper_Datetime::time(time() + $keepTime + 5);
        
        if ($shakeDto->id) {
            $this->shakeDao->update($shakeDto);
        } else {
            $this->shakeDao->insert($shakeDto);
        }
        
        return $shakeDto;
    }
    
    /**
     * Return current shake instance of Dto that is currenty not completed
     * @param Dto_User $userDto
     * @return Dto_Shake | null
     */
    public function getShakeDto(Dto_User $userDto)
    {
    	return $this->shakeDao->fetchOneBy(array(
    		'user_id' => $userDto->id,
    		'completed_at >=' => Qsoft_Helper_Datetime::current(),
    	));
    }
    
    /**
     * Do look up data that not expried
     * 
     * @param Dto_Shake $shakeDto
     * @param Dto_User $userDto
     * @return array
     */
    protected function _doLookUp(Dto_Shake $shakeDto, Dto_User $userDto, $limitDistance)
    {
        return $this->shakeDao->inExpiredTimeAndDistance($shakeDto, $userDto, $limitDistance);
    }
    
}