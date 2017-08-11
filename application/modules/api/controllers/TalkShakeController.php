<?php

class Api_TalkShakeController extends Qsoft_Rest_Controller
{

    protected $_daoClass = 'Dao_Talkshake';

    /**
     * UseItem
     */
    public function useItemAction()
    {
		$userDto = Zend_Registry::get('api_user');
		$userId = trim($this->_getParam('user_id', ''));
		$function = trim($this->_getParam('function, 0'));
		$id = trim($this->_getParam('id', ''));
		
		$user_dao = new Dao_User();
		
		if ($userId) {
			$user = $user_dao->fetchOnePublicBy('unique_id', $userId);
			$userId = $user->id;
		} else
			$userId = $userDto->id;
		
		
		
		
		
		
		
		
    }
	/**
	 * Evaluation
	 * ok
	 */
    public function evaluationAction()
    {
		$userDto = Zend_Registry::get('api_user');
		$userId = trim($this->_getParam('user_id', ''));
		$valuationId = trim($this->_getParam('valuation_id', ''));
		$user_dao = new Dao_User();
		$targetUser = $user_dao->fetchOnePublicBy('unique_id', $userId);
		
		if (!$targetUser)
			$this->failure(ERROR_USER_NOT_FOUND);
		else {
			if ($this->getDao()->valuation($userDto, $targetUser, $valuationId))
				$this->success(array());
			else
				$this->failure(ERROR_TALKSHAKE_VALUATION);
		}
    }
	/**
	 * GetReportTemplate
	 * ok
	 */
    public function getReportTemplateAction()
    {
		$data['data'] = $this->getDao()->getReportTemplate();
		$this->success($data);
    }
	/**
	 * Report
	 * 
	 */
    public function reportAction()
    {
		$userDto = Zend_Registry::get('api_user');
		$userId = trim($this->_getParam('user_id', ''));
		$reason = trim($this->_getParam('reason', 1));
		
		$user_dao = new Dao_User();
		if ($userId)
			$target = $user_dao->fetchOnePublicBy('unique_id', $userId);

		if (!$target)
			$this->failure(ERROR_USER_NOT_FOUND);
		else {
			if ($this->getDao()->insertReport($userDto->id, $target->id, $reason))
				$this->success(array());
			else
				$this->failure(ERROR_TALKSHAKE_REPORT);
		}
    }
	/**
	 * StartShake
	 * ok
	 */
    public function startShakeAction()
    {
		$userDto = Zend_Registry::get('api_user');
		$userId = trim($this->_getParam('user_id', ''));
		$partnerUserId = trim($this->_getParam('partner_user_id', ''));
		
		$user_dao = new Dao_User();
		$user = $user_dao->fetchOnePublicBy('unique_id', $userId);
		$part = $user_dao->fetchOnePublicBy('unique_id', $partnerUserId);
		if (!$user || !$part)
			$this->failure(ERROR_USER_NOT_FOUND);
		else {
			if ($id = $this->getDao()->startShake($user, $part))
				$this->success(array("data" => $id));
			else
				$this->failure(ERROR_TALKSHAKE_START_SHAKE);
		}
    }
	/**
	 * EndShake
	 * ok
	 */
    public function endShakeAction()
    {
		$userDto = Zend_Registry::get('api_user');
		$shakeLogId = trim($this->_getParam('shake_log_id', ''));
		if (!$shakeLogId)
			$this->failure(ERROR_SHAKE_LOG_ID_INVALID);
		else {
			if ($this->getDao()->endShake($shakeLogId))
				$this->success(array());
			else
				$this->failure(ERROR_TALKSHAKE_END_SHAKE);
		}
    }
	/**
	 * StartNow
	 * 
	 */
    public function startNowAction()
    {
		$userDto = Zend_Registry::get('api_user');
		$userId = trim($this->_getParam('user_id', ''));
		$partnerUserId = trim($this->_getParam('partner_user_id', ''));
		
		$user_dao = new Dao_User();
		$user = $user_dao->fetchOnePublicBy('unique_id', $userId);
		$part = $user_dao->fetchOnePublicBy('unique_id', $partnerUserId);
		
		if (!$user || !$part)
			$this->failure(ERROR_USER_NOT_FOUND);
		else {
			if ($id = $this->getDao()->startNow($user, $part))
				$this->success(array("data" => $id));
			else
				$this->failure(ERROR_TALKSHAKE_START_NOW);
		}
    }
	/**
	 * EndNow
	 * 
	 */
    public function endNowAction()
    {
		$userDto = Zend_Registry::get('api_user');
		$nowLogId = trim($this->_getParam('now_log_id', ''));
		if (!$nowLogId)
			$this->failure(ERROR_NOW_LOG_ID_INVALID);
		else {
			if ($this->getDao()->endNow($nowLogId))
				$this->success(array());
			else
				$this->failure(ERROR_TALKSHAKE_END_SHAKE);
    	}
	}
	/**
	 * MachingStart
	 * 
	 */
    public function machingStartAction()
    {
		$userDto = Zend_Registry::get('api_user');
		$userId = trim($this->_getParam('user_id', ''));
		$function = trim($this->_getParam('function', 0));
		$user_dao = new Dao_User();
		$user = $user_dao->fetchOnePublicBy('unique_id', $userId);
		if (!$user)
			$this->failure(ERROR_USER_NOT_FOUND);
		else {
			if ($this->getDao()->machingStart($user, $function))
				$this->success(array());
			else
				$this->failure(ERROR_TALKSHAKE_MACHING_START);
		}
    }
	/**
	 * MachingEnd
	 * 
	 */
    public function machingEndAction()
    {
		$userDto = Zend_Registry::get('api_user');
		$userId = trim($this->_getParam('user_id', ''));
		$function = trim($this->_getParam('function', 0));
		
		$user_dao = new Dao_User();
		$user = $user_dao->fetchOnePublicBy('unique_id', $userId);
		if (!$user)
			$this->failure(ERROR_USER_NOT_FOUND);
		else {
			if ($this->getDao()->machingEnd($user, $function))
				$this->success(array());
			else
				$this->failure(ERROR_TALKSHAKE_MACHING_END);
		}
    }
	/**
	 * MachingSearch
	 * 
	 */
    public function machingSearchAction()
    {
		$userDto = Zend_Registry::get('api_user');
		$userId = trim($this->_getParam('user_id', ''));
		$function = trim($this->_getParam('function', 0));
		$user_dao = new Dao_User();
		$user = $user_dao->fetchOnePublicBy('unique_id', $userId);
		if (!$user)
			$this->failure(ERROR_USER_NOT_FOUND);
		else {
			try {
				$data['data'] = $this->getDao()->machingSearch($user, $function);
			} catch (Exception $e) {
				$this->failure($e->getMessage());
			}
			$this->success($data);
		}
		
		
    }
	/**
	 * GetAvailableinfo
	 * 
	 */
    public function getAvailableinfoAction()
    {
		$data['data'] = $this->getDao()->getAvailableinfo();
		$this->success($data);
    }

   /**
     * GetDiceInfo
     */
	public function getDiceInfoAction()
	{
		$data['data'] = $this->getDao()->getDice();
		$this->success($data);
	}

}
