<?php

class Api_ShopController extends Qsoft_Rest_Controller
{

    protected $_daoClass = 'Dao_Shop';

    /**
     * GetAllShopInfo
	 * shopにある全てのアイテム
     */
    public function indexAction()
    {
		
		// library/Qsoft/Controller/Plugin/Rest/Auth.phpで認証されているので不要
		$userDto = Zend_Registry::get('api_user');
		$userId = trim($this->_getParam('user_id', ''));
		$user_dao = new Dao_User();
		
		if ($userId) {
			$user = $user_dao->fetchOnePublicBy('unique_id', $userId);
			$userId = $user->id;
			if (!$user)
				$this->failure(ERROR_USER_NOT_FOUND);
		} else
			$userId = $userDto->id;
		
		//ショップの全アイテムを取得
        $shopItem['data'] = $this->getDao()->fetchAll($userId);
		/**
		 * $shopItem
		 * array(
		 *  "gifts" => array(array(),...)
		 *  "items" => array(array(),...)
		 *  "stamps" => array(array(),...)
		 * )
		 */
        $this->success($shopItem);
    }
	/**
	 * GetAllShopInfo
	 * ユーザーが保有している全てのアイテム
	 */
    public function allAction()
    {
		
		// library/Qsoft/Controller/Plugin/Rest/Auth.phpで認証されているので不要
		//$userDto = Zend_Registry::get('api_user');
		//exit($userDto->getChatUsername());
		$userId = trim($this->_getParam('user_id', ''));
		$userDto = Zend_Registry::get('api_user');
		if (!$userId)
			$userId = $userDto->id;
		else {
			$user_dao = new Dao_User();
			$user = $user_dao->fetchOnePublicBy('unique_id', $userId);
			if (!$user)
				$this->failure(ERROR_USER_NOT_FOUND);
			$userId = $user->id;
		}
		//ユーザーが保有している全てのアイテム
        $allItem['data'] = $this->getDao()->fetchAllUser($userId);
		/**
		 * $shopItem
		 * array(
		 *  "gifts" => array(array(),...)
		 *  "items" => array(array(),...)
		 *  "stamps" => array(array(),...)
		 * )
		 */
        $this->success($allItem);
    }
	
	
     /**
     * GetUser GiftInfo
     */
	public function giftInfoAction()
	{
        $userId = trim($this->_getParam('user_id', ''));
		$userDto = Zend_Registry::get('api_user');
		if (!$userId)
			$userId = $userDto->id;
		else {
			$user_dao = new Dao_User();
			$user = $user_dao->fetchOnePublicBy('unique_id', $userId);
			if (!$user)
				$this->failure(ERROR_USER_NOT_FOUND);
			$userId = $user->id;
		}
		$data = array();
		$data['gifts'] = $this->getDao()->getGifts($userId);
		$this->success($data);
	}
    /**
     * GetUserItemInfo
     */
	public function itemInfoAction()
	{
        $userId = trim($this->_getParam('user_id', ''));
		$userDto = Zend_Registry::get('api_user');
		if (!$userId)
			$userId = $userDto->id;
		else {
			$user_dao = new Dao_User();
			$user = $user_dao->fetchOnePublicBy('unique_id', $userId);
			if (!$user)
				$this->failure(ERROR_USER_NOT_FOUND);
			$userId = $user->id;
		}
		$data = array();
		$data['items'] = $this->getDao()->getItems($userId);
		$this->success($data);
	}
    /**
     * GetUserStampInfo
     */
	public function stampInfoAction()
	{
        $userId = trim($this->_getParam('user_id', ''));
		$userDto = Zend_Registry::get('api_user');
		if (!$userId)
			$userId = $userDto->id;
		else {
			$user_dao = new Dao_User();
			$user = $user_dao->fetchOnePublicBy('unique_id', $userId);
			if (!$user)
				$this->failure(ERROR_USER_NOT_FOUND);
			$userId = $user->id;
		}
		$data = array();
		$data['stamps'] = $this->getDao()->getStamps($userId);
		$this->success($data);
	}



   /**
     * SetUserStampInfo
     */
	public function setStampAction()
	{
        $post = $this->getRequest()->getPost();
		$userDto = Zend_Registry::get('api_user');
		$stampId = $post['stamp_id'];
		if ($this->getDao()->insertStamp($userDto->id, $stampId))
			$this->success(array());
		else
			$this->failure(ERROR_SHOP_SET_STAMP);
	}
    /**
     * SetUserGiftInfo
     */
	public function setGiftAction()
	{
        $post = $this->getRequest()->getPost();
		$userDto = Zend_Registry::get('api_user');
		$giftId = $post['gift_id'];
		if ($this->getDao()->insertGift($userDto->id, $giftId))
			$this->success(array());
		else
			$this->failure(ERROR_SHOP_SET_GIFT);
	}
    /**
     * SetUserItemInfo
     */
	public function setItemAction()
	{
        $post = $this->getRequest()->getPost();
		$userDto = Zend_Registry::get('api_user');
		$itemId = $post['item_id'];
		if ($this->getDao()->insertItem($userDto->id, $itemId))
			$this->success(array());
		else
			$this->failure(ERROR_SHOP_SET_ITEM);
	}

    /**
     * SendGift
     */
	public function sendGiftAction()
	{
		$result = true;
		$post = $this->getRequest()->getPost();

		$userId = $post['user_id'];
		$sendUserId = $post['send_user_id'];
		$giftId = $post['gift_id'];

		$user_dao = new Dao_User();
		$user = $user_dao->fetchOnePublicBy('unique_id', $userId);
		$part = $user_dao->fetchOnePublicBy('unique_id', $sendUserId);
		if (!$user || !$part || !is_numeric($giftId))
			if (!is_numeric($giftId))
				$this->failure(ERROR_GIFT_ID_INVALID);
			else
				$this->failure(ERROR_USER_NOT_FOUND);
		else {
			$db = Zend_Registry::get('chatAdapter');
			$db->beginTransaction();
			//giftを持っているか
			$gift = $this->getDao()->haveGift($user->id, $giftId);
			if (!$gift) {
				$result = false;
			} else {
				try {
					$result = $this->getDao()->sendGift($user->id, $part->id, $gift);
				} catch(Exception $e) {
					$db->rollBack();
					$this->failure(ERROR_SHOP_SEND_GIFT);
				}
			}
			if ($result === true) {
				$db->commit();
				$this->success();
			} else {
				$db->rollBack();
				$this->failure(ERROR_SHOP_SEND_GIFT);
			}
		}
	}
    /**
     * DiscardGift
     */
	public function discardGiftAction()
	{
		$result = true;
		$post = $this->getRequest()->getPost();

		$userId = $post['user_id'];
		$sendUserId = $post['send_user_id'];
		$giftId = $post['gift_id'];

		$user_dao = new Dao_User();
		$user = $user_dao->fetchOnePublicBy('unique_id', $userId);
		$part = $user_dao->fetchOnePublicBy('unique_id', $sendUserId);
		if (!$user || !$part || !is_numeric($giftId))
			if (!is_numeric($giftId))
				$this->failure(ERROR_GIFT_ID_INVALID);
			else
				$this->failure(ERROR_USER_NOT_FOUND);
		else {
			$db = Zend_Registry::get('chatAdapter');
			$db->beginTransaction();
			//giftを持っているか
			$gift = $this->getDao()->receiveGift($user->id, $giftId);
			if (!$gift) {
				$result = false;
			} else {
				try {
					$result = $this->getDao()->discardGift($user->id, $part->id, $gift);
				} catch(Exception $e) {
					$db->rollBack();
					$this->failure(ERROR_SHOP_DISCARD_GIFT);
				}
			}
			if ($result === true) {
				$db->commit();
				$this->success();
			} else {
				$db->rollBack();
				$this->failure(ERROR_SHOP_DISCARD_GIFT);
			}
		}
	}
    /**
     * GetItemDetail
     */
	public function itemDetailAction()
	{
        $itemId = trim($this->_getParam('item_id', ''));
		$data['item'] = $this->getDao()->getItem($itemId);
		if ($data['item'])
			$this->success($data);
		else
			$this->failure(ERROR_SHOP_NOT_FOUNT_ITEM);
	}
    /**
     * GetStampDetail
     */
	public function stampDetailAction()
	{
        $stampId = trim($this->_getParam('stamp_id', ''));
		$data['stamp'] = $this->getDao()->getStamp($stampId);
		if ($data['stamp'])
			$this->success($data);
		else
			$this->failure(ERROR_SHOP_NOT_FOUNT_STAMP);
	}
    /**
     * GetGiftDetail
     */
	public function giftDetailAction()
	{
        $giftId = trim($this->_getParam('gift_id', ''));
		$data['gift'] = $this->getDao()->getGift($giftId);
		if ($data['gift'])
			$this->success($data);
		else
			$this->failure(ERROR_SHOP_NOT_FOUNT_GIFT);
	}
	
	/**
	 * 送信されたプレゼント一覧
	 *
	 */
	public function getReceivePresentAction()
	{
		$userId = trim($this->_getParam('user_id', ''));
		$userDto = Zend_Registry::get('api_user');
		if (!$userId)
			$userId = $userDto->id;
		else {
			$user_dao = new Dao_User();
			$user = $user_dao->fetchOnePublicBy('unique_id', $userId);
			if (!$user)
				$this->failure(ERROR_USER_NOT_FOUND);
			$userId = $user->id;
		}
		$data['data'] = $this->getDao()->getPresentGift($userId);
		$this->success($data);
	}
	/**
	 * 送信されたプレゼントを受け取る
	 *
	 */
	public function receivedPresentAction()
	{
		$userId = trim($this->_getParam('user_id', ''));
		$userDto = Zend_Registry::get('api_user');
		if (!$userId)
			$userId = $userDto->id;
		else {
			$user_dao = new Dao_User();
			$user = $user_dao->fetchOnePublicBy('unique_id', $userId);
			if (!$user)
				$this->failure(ERROR_USER_NOT_FOUND);
			$userId = $user->id;
		}
		if (!$this->getDao()->receivedGift($userId))
			$this->failure(ERROR_SHOP_RECEIVED_GIFT);
		else
			$this->success(array());
	}
	
    /**
     * SetShowedGift
     */
	public function setShowedGiftAction()
	{
		$post = $this->getRequest()->getPost();
		$giftIds = $post['gift_id'];
		$userId = $post['user_id'];
		$userDto = Zend_Registry::get('api_user');
		if (!$userId)
			$userId = $userDto->id;
		else {
			$user_dao = new Dao_User();
			$user = $user_dao->fetchOnePublicBy('unique_id', $userId);
			if (!$user)
				$this->failure(ERROR_USER_NOT_FOUND);
			$userId = $user->id;
		}
		$db = Zend_Registry::get('chatAdapter');
		$db->beginTransaction();
		try {
			if (!$this->getDao()->showedGift($userId, $giftIds))
				throw new Exception();
			$db->commit();
			$this->success();
		} catch (Exception $e) {
			$db->rollBack();
			$this->failure();
		}
	}
	
	
    /**
     * GetPoint
     */
	public function getPointAction()
	{
        $userId = trim($this->_getParam('user_id', ''));
		$userDto = Zend_Registry::get('api_user');
		if (!$userId) {
			$point = $userDto->point;
		} else {
			$user_dao = new Dao_User();
			$user = $user_dao->fetchOnePublicBy('unique_id', $userId);
			if (!$user)
				$this->failure(ERROR_USER_NOT_FOUND);
			$point = $user->point;
		}
		
		$this->success(array("point" => $point));
	}
    /**
     * GetPointMaster
     */
	public function getPointMasterAction()
	{
		$data['data'] = $this->getDao()->getPointMaster();
		$this->success($data);
	}
    /**
     * SpendPoint
     */
	public function spendPointAction()
	{
		$result = true;
		$post = $this->getRequest()->getPost();
		$userId = $post['user_id'];
		$point = $post['spend_point'];
		$userDto = Zend_Registry::get('api_user');
		if (!$userId)
			$userId = $userDto->id;
		else {
			$user_dao = new Dao_User();
			$user = $user_dao->fetchOnePublicBy('unique_id', $userId);
			if (!$user)
				$this->failure(ERROR_USER_NOT_FOUND);
			$userId = $user->id;
		}
		
		if ($this->getDao()->spendPoint($userId, $point)) {
			$this->success(array());
		} else
			$this->failure(ERROR_SHOP_SPEND_POINT);
	}
    /**
     * addPoint
     */
	public function addPointAction()
	{
		$result = true;
		$post = $this->getRequest()->getPost();
		$userId = $post['user_id'];
		$point = $post['add_point'];
		$userDto = Zend_Registry::get('api_user');
		if (!$userId)
			$userId = $userDto->id;
		else {
			$user_dao = new Dao_User();
			$user = $user_dao->fetchOnePublicBy('unique_id', $userId);
			if (!$user)
				$this->failure(ERROR_USER_NOT_FOUND);
			$userId = $user->id;
		}


		if ($this->getDao()->addPoint($userId, $point)){
			$this->success(array());
		}else
			$this->failure(ERROR_SHOP_ADD_POINT);
	}
	
	/**
	 * getRouletteMaster
	 */
	public function getRouletteMasterAction()
	{
		$data['data'] = $this->getDao()->getRouletteMaster();
		$this->success($data);
	}
	
	/**
	 * GetPicollection
	 *
	 */
	public function getPicollectionAction()
	{
        $userId = trim($this->_getParam('user_id', ''));
		$userDto = Zend_Registry::get('api_user');
	
		//
		$data['data'] = $this->getDao()->getPicollection($userDto->id);
		$this->success($data);
	}

}
