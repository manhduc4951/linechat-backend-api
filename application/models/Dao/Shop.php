<?php


class Dao_Shop extends Zend_Db_Table_Abstract
{
    protected $_name = 'item';
	protected $_db;
	protected $atItem;
	protected $imgPathItem = "http://122.255.76.176/LCA450_backend/public/uploads/item/image/";
	protected $imgPathGift = "http://122.255.76.176/LCA450_backend/public/uploads/gift/image/";
	protected $imgPathStamp = "http://122.255.76.176/LCA450_backend/public/uploads/stamp/image/";
    
//    protected $_rowClass = 'Dto_Shop';
	public function __construct()
	{
		$this->_db = Zend_Registry::get('chatAdapter');
	}
    public function fetchAll($userId="")
    {
# 		if ($userId)
# 			return $this->fetchAllUser($userId);
		//item
        $db = Zend_Registry::get('chatAdapter');
		$sql = "select
						i.item_id,
						i.item_type,
						i.item_title,
						i.comment,
						concat('".$this->imgPathItem."',i.item_img) as item_img,
						i.usage_count,
						i.expiration_date,
						i.point,
						(select count(*) from user_item where usage_count > 0 and item_id = i.item_id and user_id = ".$db->quote($userId).") as item_purchased_num
					from
						item as i
					left join user_item ui
					on i.item_id = ui.item_id
					group by i.item_id";
		$st = $db->query($sql);
		$items = $st->fetchAll();
		//gift
		$sql = "select
						g.gift_category_id,
						g.gift_id,
						g.gift_title,
						concat('".$this->imgPathGift."',g.gift_img) as gift_img,
						g.point,
						(select count(*) from user_gift where approved > 0 and gift_id = g.gift_id and user_id = ".$db->quote($userId).") as gift_purchased_num
					from
						gift as g
					left join user_gift as ug
					on g.gift_id = ug.gift_id
					group by g.gift_id";
		$st = $db->query($sql);
		$gifts = $st->fetchAll();
		//stamp
		$sql = "select
						s.stamp_id,
						s.stamp_name,
						concat('".$this->imgPathStamp."small/',s.stamp_small_image) as stamp_small_image,
						s.point,
						concat('".$this->imgPathStamp."sample/',s.stamp_sample_image) as stamp_sample_image,
						(select count(*) from user_stamp where stamp_id = s.stamp_id and user_id = ".$db->quote($userId).") as stamp_purchased_num
					from
						stamp as s
					left join user_stamp as us
					on s.stamp_id = us.stamp_id
					where
						s.show_hide != 0
					group by s.stamp_id";
		$st = $db->query($sql);
		$stamps = $st->fetchAll();
		
		return array("items"=>$items, "gifts"=>$gifts, "stamps"=>$stamps);
		
		exit(var_dump($d));
		
		$select = $this->select();
# 			->from($this->name, false)
# 			->setIntegrityCheck(false);
        
        $d = $this->fetchAll($select);
		var_dump($d);
    }
	public function fetchAllUser($userId)
	{
		if (!$userId)
			return false;
		//item
		$sql = "select
					ui.item_id,
					i.item_type,
					i.item_title,
					i.comment,
					concat('".$this->imgPathItem."',i.item_img) as item_img,
					ui.usage_count,
					ui.expired_date,
					i.point,
					i.created_at
				from
					user_item as ui, item as i
				where
					i.item_id = ui.item_id and
					ui.user_id = ".$this->_db->quote($userId)." and
					ui.usage_count > 0";
		$st = $this->_db->query($sql);
		$data['items'] = $st->fetchAll();
		//gift
		$sql = "select
					g.gift_category_id,
					ug.gift_id,
					g.gift_title,
					concat('".$this->imgPathGift."',g.gift_img) as gift_img,
					ug.present_user_id,
					u.nick_name,
					ug.created_at,
					ug.showed,
					g.point
				from
					gift as g,user_gift as ug left join
					user as u
				on u.id = ug.present_user_id
				where
					ug.gift_id = g.gift_id and
					ug.user_id = ".$this->_db->quote($userId)." and
					ug.approved = '1'";
		$st = $this->_db->query($sql);
		$data['gifts'] = $st->fetchAll();
		//stamp
		$sql = "select
					us.stamp_id,
					s.stamp_name,
					concat('".$this->imgPathStamp."small/',s.stamp_small_image) as stamp_small_image,
					s.point
				from
					user_stamp as us, stamp as s
				where
					us.stamp_id = s.stamp_id and
					us.user_id = ".$this->_db->quote($userId);
		$st = $this->_db->query($sql);
		$data['stamps'] = $st->fetchAll();
		
		return $data;
	}
	
	public function getGift($giftId)
	{
		if (!is_numeric($giftId))
			return false;
        $db = Zend_Registry::get('chatAdapter');
		$st = $db->query("select gift_id,gift_title,concat('".$this->imgPathGift."',gift_img) as gift_img,point from gift where gift_id = ".$db->quote($giftId));
		return $st->fetch();
		
	}
	public function getItem($itemId)
	{
		if (!is_numeric($itemId))
			return false;
        $db = Zend_Registry::get('chatAdapter');
		$st = $db->query("select item_id,item_title,concat('".$this->imgPathItem."',item_img) as item_img,comment,point from item where item_id = ".$db->quote($itemId));
		return $st->fetch();
		
	}
	public function getStamp($stampId)
	{
		if (!is_numeric($stampId))
			return false;
        $db = Zend_Registry::get('chatAdapter');
		$st = $db->query("select stamp_id,stamp_name from stamp where stamp_id = ".$db->quote($stampId)." and show_hide != 0");
		return $st->fetch();
		
	}
	public function getGifts($userId)
	{
		if (!is_numeric($userId))
			return false;
        $db = Zend_Registry::get('chatAdapter');
		$st = $db->query("select gift_id from user_gift where user_id = ".$db->quote($userId)." and approved = 1");
		return $st->fetchAll();
		
	}
	public function getItems($userId)
	{
		if (!is_numeric($userId))
			return false;
        $db = Zend_Registry::get('chatAdapter');
		$st = $db->query("select item_id from user_item where user_id = ".$db->quote($userId)." and usage_count > 0");
		return $st->fetchAll();
		
	}
	public function getStamps($userId)
	{
		if (!is_numeric($userId))
			return false;
        $db = Zend_Registry::get('chatAdapter');
		$st = $db->query("select stamp_id from user_stamp where user_id = ".$db->quote($userId));
		return $st->fetchAll();
		
	}
	
	public function getPresentGift($userId)
	{
		if (!is_numeric($userId))
			return false;
		$st = $this->_db->query("select gift_id from user_gift where user_id = ".$this->_db->quote($userId)." and present_user_id > 0");
		return $st->fetchAll();
	}
	
	public function getPointMaster()
	{
		$st = $this->_db->query("select id,point,price,android_item_id,ios_item_id from point_master");
		return $st->fetchAll();
	}
	
	public function getRouletteMaster()
	{
		$st = $this->_db->query("select roulette_id,type,item_id,gift_id,point,title,odds from roulette");
		return $st->fetchAll();
	}
	
	public function haveGift($userId, $giftId)
	{
		if (!is_numeric($userId) || !is_numeric($giftId))
			return false;
		$st = $this->_db->query("select * from user_gift where user_id = ".$this->_db->quote($userId)." and gift_id = ".$this->_db->quote($giftId)." and approved = 1");
		return $st->fetch();
	}
	public function receiveGift($userId, $giftId)
	{
		if (!is_numeric($userId) || !is_numeric($giftId))
			return false;
		$st = $this->_db->query("select * from user_gift where user_id = ".$this->_db->quote($userId)." and gift_id = ".$this->_db->quote($giftId));
		return $st->fetch();
	}
	public function receivedGift($userId)
	{
		if (!is_numeric($userId))
			return false;
		return $this->_db->query("update user_gift set approved = '1' where user_id = ".$this->_db->quote($userId)." and approved = '0'");
	}
	public function sendGift($userId, $sendUserId, $gift)
	{
		if (!is_numeric($userId) || !is_numeric($sendUserId))
			return false;
		if (!$gift)
			return false;
		//add
		$this->_db->query("insert into user_gift values (now(), now(), '', $sendUserId, $gift->gift_id, $userId, 1, 0)");
		$inId = $this->_db->lastInsertId();
		//sub
		if ($inId)
			$this->_db->query("delete from user_gift where id = ".$this->_db->quote($gift->id));
		else
			return false;
		return true;
	}
	public function discardGift($userId, $sendUserId, $gift)
	{
		if (!is_numeric($userId) || !is_numeric($sendUserId))
			return false;
		if (!$gift)
			return false;
		//sub
		$st = $this->_db->query("delete from user_gift where gift_id = ".$this->_db->quote($gift->gift_id)." and user_id = ".$this->_db->quote($userId)." and present_user_id = ".$this->_db->quote($sendUserId)." and approved = '0'");
		if ($st->rowCount())
			return true;
		else
			return false;
		
	}
	
	public function insertStamp($userId, $stampId)
	{
		if (!is_numeric($userId) || !is_numeric($stampId))
			return false;
		if (!$this->_db->query("insert into user_stamp values (now(), now(), '', $userId, $stampId)"))
			return false;
		else
			return true;
	}
	public function insertItem($userId, $itemId)
	{
		if (!is_numeric($userId) || !is_numeric($itemId))
			return false;
		//itemî•ñŽæ“¾
		$st = $this->_db->query("select * from item where item_id = ".$this->_db->quote($itemId));
		$item = $st->fetch();
		if (!$item)
			return false;
		if (!$this->_db->query("insert into user_item values (now(), now(), '', $userId, $itemId, $item->usage_count, $item->expiration_date)"))
			return false;
		else
			return true;
	}
	public function insertGift($userId, $giftId)
	{
		if (!is_numeric($userId) || !is_numeric($giftId))
			return false;
		if (!$this->_db->query("insert into user_gift values (now(), now(), '', $userId, $giftId, '', '1', '1')"))
			return false;
		else
			return true;
	}
	
	public function showedGift($userId, $giftIds)
	{
		if (!is_numeric($userId) || !$giftIds)
			return false;
		$ids = explode(",", $giftIds);
		if (is_array($ids)) {
			$st = $this->_db->prepare("update user_gift set showed = '1' where user_id = ".$this->_db->quote($userId)." and gift_id = ?");
			foreach ($ids as $id) {
				$st->execute(array($id));
			}
		}
		return true;
	}
	
	public function spendPoint($userId, $point)
	{
		if (!is_numeric($userId) || !is_numeric($point))
			return false;
		$userDao = new Dao_User();
		$user = $userDao->fetchOnePublic($userId);
		if ($user->point < $point)
			return false;
//		exit(var_dump($user));
		$data = array('point' => ($user->point-$point));
		return $this->_db->update("user", $data, "id = ".$this->_db->quote($user->id));;
	}
	public function addPoint($userId, $point)
	{
		if (!is_numeric($userId) || !is_numeric($point))
			return false;
		$userDao = new Dao_User();
		$user = $userDao->fetchOnePublic($userId);
		$data = array('point' => ($userDao->point+$point));
		return $this->_db->update("user", $data, "id = ".$this->_db->quote($user->id));;
	}
	
	public function getPicollection($userId)
	{
		if (!$userId)
			return false;
		$sql = "select
						h.id as hatochan_id,
						hatochan_image,
						hatochan_small_image,
						hatochan_detail_image
					from
						user_hatochan_collection as u,hatochan_master as h
					where
						u.hatochan_id = h.id and
						u.user_id = ".$this->_db->quote($userId);
		$st = $this->_db->query($sql);
		$data['data'] = $st->fetchAll();
		
		return $data;
	}
}