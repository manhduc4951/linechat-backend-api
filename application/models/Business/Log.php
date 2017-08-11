<?php

/**
 * Business_Log class
 * 
 * @package LineChatApp
 * @subpackage Business
 * @author duyld
 */
class Business_Log
{
    
    /**
     * App start log Dao
     * 
     * @var Dao_AppStartLog
     */
    protected $appStartLogDao;
    
    /**
     * Singleton pattern
     * 
     * @var Business_Log
     */
    static protected $instance;
    
    /**
     * Apply singleton pattern to use only one Log business instance
     * 
     * @return  Business_Log
     */
    public static function getInstance()
    {
        if ( ! self::$instance) {
            self::$instance = new Business_Log();
        }
        
        return self::$instance;
    }
    
    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct()
    {
        $this->appStartLogDao = new Dao_AppStartLog;
    }
    
    /**
     * Logging user login info
     * 
     * @param   Dto_User    $userDto
     * @return  boolean
     */
    public function appStartLog(Dto_User $userDto)
    {
        $appStartLogDto = new Dto_AppStartLog();
        $appStartLogDto->user_id = $userDto->id;
        
        try {
            $this->appStartLogDao->insert($appStartLogDto);
        } catch (Exception $e) {
            Zend_Registry::get('log')->log(
                'Cannot write log for app start with user id' . $userDto->id,
                Zend_Log::ERR
            );
            
            return false;
        }
        
        return true;
    }
    
}
