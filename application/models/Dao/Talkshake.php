<?php


class Dao_Talkshake extends Zend_Db_Table_Abstract
{
    protected $_name = 'user';
	protected $_db;
	protected $val = array();
	protected $cal;
    
//    protected $_rowClass = 'Dto_TalkShake';
	public function __construct()
	{
		$this->_db = Zend_Registry::get('chatAdapter');
		$this->val[1] = 2;
		$this->val[2] = 4;
		$this->val[3] = -4;
		$this->val[4] = -1;
		$this->val[5] = 0;
		$this->val[6] = 0;
		
	}
	public function fetchAll($userId=""){}
	
	
	public function valuation($userDto, $targetUserDto, $valId)
	{
		if (!$targetUserDto)
			return false;
		if (!$valId || !is_numeric($valId))
			return false;
		if ($userDto->valuation > 80) {
			$cal = 2;
		} else if ($userDto->valuation > 60) {
			$cal = 1.5;
		} else if ($userDto->valuation > 40) {
			$cal = 1;
		} else if ($userDto->valuation > 20) {
			$cal = 0.5;
		} else {
			$cal = 0.1;
		}
		if ($this->val[$valId]!=0) {
			$pt = $this->val[$valId] * $cal;
			$updatePt = $targetUserDto->valuation + $pt;
			if ($updatePt > 100)
				$updatePt = 100;
			else if ($updatePt < 0)
				$updatePt = 0;
		}
		$sql = "update user set valuation = ".$this->_db->quote($updatePt)." where id = ".$this->_db->quote($targetUserDto->id);
		return $this->_db->query($sql);
		
	}
	
	public function insertReport($userId, $targetId, $reason)
	{
		$sql = "insert into user_report
					(report_user_id,target_user_id,content,created_at,updated_at) values
					(
					{$userId},
					{$targetId},
					(select reason from report_master where id = {$reason}),
					now(),
					now()
					)";
		$this->_db->query($sql);
		return $this->_db->lastInsertId();
	}
	
	public function startShake($user, $part)
	{
		if (!$user || !$part)
			return false;
		$sql = "insert into shake_log values (now(), now(), '', {$user->id}, {$part->id}, 0, 0)";
		$this->_db->query($sql);
		return $this->_db->lastInsertId();
	}
	public function endShake($logId)
	{
		if (!$logId || !is_numeric($logId))
			return false;
		$sql = "update shake_log set shake_end_at = now() where id = ".$this->_db->quote($logId);
		$st = $this->_db->query($sql);
		return $st->rowCount();
	}
	
	public function startNow($user, $part)
	{
		if (!$user || !$part)
			return false;
		$sql = "insert into now_log values (now(), now(), '', {$user->id}, {$part->id}, 0, 0, 0)";
		$this->_db->query($sql);
		return $this->_db->lastInsertId();
	}
	public function endNow($logId)
	{
		if (!$logId || !is_numeric($logId))
			return false;
		$sql = "update now_log set talk_end_at = now() where now_id = ".$this->_db->quote($logId);
		$st = $this->_db->query($sql);
		return $st->rowCount();
	}
	
	public function machingStart($user, $type)
	{
		if (!$user)
			return false;
		switch($type) {
		case 0:
			$column = "shake_matching_ok";
			break;
		case 1:
			$column = "now_matching_ok";
			break;
		default:
			return false;
		}
		$sql = "update user set ".$column." = '1' where id = ".$this->_db->quote($user->id);
		$st = $this->_db->query($sql);
		return $st->rowCount();
	}
	public function machingEnd($user, $type)
	{
		if (!$user)
			return false;
		switch($type) {
		case 0:
			$column = "shake_matching_ok";
			break;
		case 1:
			$column = "now_matching_ok";
			break;
		default:
			return false;
		}
		$sql = "update user set ".$column." = '0' where id = ".$this->_db->quote($user->id);
		$st = $this->_db->query($sql);
		return $st->rowCount();
	}
	public function machingSearch($user, $type)
	{
		if (!$user)
			return false;
		switch($type) {
		case 0:
			$column = "shake_matching_ok";
			break;
		case 1:
			$column = "now_matching_ok";
			break;
		default:
			return false;
		}
		//xmlŽæ“¾
		$sql = "select list from ofPrivacyList where username = '174884c3c5451d58'";//".$this->_db->quote($user->user_id);
		$st = $this->_db->query($sql);
		$xml = $st->fetch();
		$xmlArray = simplexml_load_string($xml->list);
		if (is_object($xmlArray)) {
			foreach ($xmlArray as $arr) {
				if ($arr['value']) {
					if (strpos("@", $arr['value']))
						list($id, $name) = explode("@", $arr['value']);
					else
						$id = $arr['value'];
					$deny[] = "'".$id."'";
				}
			}
		}
		
		$sql = "select unique_id,nick_name from user
						where
							".$column." = 1 and
							".(is_array($deny)? "user_id NOT IN(".implode(",", $deny).") and": "")."
							id != ".$this->_db->quote($user->id)." and
							longitude IS NULL != 1 and
							latitude IS NULL != 1
							
						order by ABS(latitude-{$user->latitude})+ABS(longitude-{$user->longitude}) asc
						limit 1";
		if (!$st = $this->_db->query($sql))
			throw new Exception(112);
		return $st->fetch();
	}
	
	public function getReportTemplate()
	{
		$st = $this->_db->query("select id,reason from report_master");
		return $st->fetchAll();
	}
	public function getAvailableinfo()
	{
		$st = $this->_db->query("select available_count,recovery_time,recovery_amount from contents_limit");
		return $st->fetchAll();
	}
	public function getDice()
	{
		$array = array("dice_title1","dice_title2","dice_title3","dice_title4","dice_title5","dice_title6");
		shuffle($array);
		$st = $this->_db->query("select ".implode(",",$array)." from dice");
		return $st->fetchAll();
	}
	
}