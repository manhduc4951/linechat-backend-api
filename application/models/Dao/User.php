<?php

/**
 * Dao_User
 *
 * @package Dao
 * @author duyld
 */
class Dao_User extends Qsoft_Db_Table_Abstract
    implements Dao_Interface_EntityAccessable, Dao_Interface_PublicAccessable
{

    protected $_name = 'user';

    protected $_rowClass = 'Dto_User';

    protected $imageDao;

    /**
     * Constructor.
     *
     * Supported params for $config are:
     * - db              = user-supplied instance of database connector,
     *                     or key name of registry instance.
     * - name            = table name.
     * - primary         = string or array of primary key(s).
     * - rowClass        = row class name.
     * - rowsetClass     = rowset class name.
     * - referenceMap    = array structure to declare relationship
     *                     to parent tables.
     * - dependentTables = array of child tables.
     * - metadataCache   = cache for information from adapter describeTable().
     *
     * @param  mixed $config Array of user-specified config options, or just the Db Adapter.
     * @return void
     */
    public function __construct($config = array())
    {
        parent::__construct();

        $this->imageDao = new Dao_UserImage();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchOnePublic($identity)
    {
        $select = $this->select()
            ->where($this->getPrimaryKey() . ' = ?', $identity)
            ->where('state = ?', Dto_User::STATE_ACTIVE);

        return $this->fetchRow($select);
    }

    /**
     * Retrieve a public instance by provided name and value
     * Auto retrieve the user image
     *
     * @param   string  $name
     * @param   string  $value
     * @return  Dto_User
     */
    public function fetchOnePublicBy($name, $value)
    {//echo '<pre>'; print_r($value); echo '</pre>'; die;
        $select = $this->select()
            ->where($name . ' = ?', $value)
            ->where('state = ?', Dto_User::STATE_ACTIVE);

        $userDto = $this->fetchRow($select);

        if ( ! $userDto OR ! $userDto->profile_user_img_id) {
            return $userDto;
        }

        // get the image
        // do not use join for best performance
        $imageDto = $this->imageDao->fetchOneBy('user_img_id', $userDto->profile_user_img_id);
        if ($imageDto) {
            $userDto->addColumn('user_img', $imageDto->user_img);
        }

        return $userDto;
    }

    /**
     * Retrieve all public instances
     *
     * @param   Zend_Db_Select $select
     * @return  Qsoft_Db_Table_Rowset
     */
    public function fetchPublic(Zend_Db_Select $select = null)
    {
        $select = $this->select()
            ->where('state = ?', Dto_User::STATE_ACTIVE);
        return $this->fetchAll($select);
    }

    /**
     * Retrieve a public instance by provided name and value
     *
     * @param   string  $name
     * @param   string  $value
     * @return  Qsoft_Db_Table_Rowset
     */
    public function fetchPublicBy($name = null, $value = null)
    {
        $select = $this->select()
            ->where('state = ?', Dto_User::STATE_ACTIVE);

        return $this->fetchAllBy($name, $value, $select);
    }

    /**
     * Add user image data to an user rowset
     *
     * @param Qsoft_Db_Table_Rowset $users
     * @return void
     */
    public function addImageData(Qsoft_Db_Table_Rowset $users)
    {
        // retrieve the user image
        // do not use join for best performance
        $imageIdArray = $users->toString('profile_user_img_id');
        $images = $this->imageDao->fetchAllBy('user_img_id', $imageIdArray);
        $imagesArray = array();
        foreach ($images as $imageDto) {
            $imagesArray[$imageDto->user_img_id] = $imageDto->user_img;
        }

        foreach ($users as $userDto) {
            if (isset($imagesArray[$userDto->profile_user_img_id])) {
                $userDto->addColumn('user_img', $imagesArray[$userDto->profile_user_img_id]);
            }
        }

        return;
    }

	/**
	 * Search public user list with provided criteria
	 *
	 * @param string|array|Zend_Db_Table_Select $criteria  OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param Dto_User                          $userDto
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @param int                               $count  OPTIONAL An SQL LIMIT count.
     * @param int                               $offset OPTIONAL An SQL LIMIT offset.
     * @return Zend_Db_Table_Rowset_Abstract The row results per the Zend_Db_Adapter fetch mode.
	 */
	public function search($criteria, Dto_User $userDto, $order = null, $count = null, $offset = null)
	{
		$select = "SELECT user.* from user";
        $where = array('state != :state_delete', 'user.id != :user_id');
        $bind = array('state_delete' => Dto_User::STATE_DELETE, 'user_id' => $userDto->id);

        // join with privacy list to remove blocked users
        $select .= " LEFT JOIN ofPrivacyList ON username = :unique_id ";
        $bind['unique_id'] = $userDto->unique_id;

        $where[] = "ofPrivacyList.list IS NULL OR ofPrivacyList.list NOT LIKE CONCAT('%', user.unique_id, '%')";

        // join with hide settings table to remove hidden users
        $select .= " LEFT JOIN user_hide_setting ON user_hide_setting.user_id = :user_id AND hide_user_id = user.id ";
        $where[] = 'user_hide_setting.user_id IS NULL';

        // Search user by distance
        if ( ! empty($criteria['distance'])) {
            // if user does not have coordinate data, his/her cannot search by distance
            // so return the empty result
            if (null === $userDto->longitude OR null === $userDto->latitude) {
                return new Qsoft_Db_Table_Rowset(array('data' => array(), 'table' => $this, 'rowClass' => 'Dto_User'));
            }

            $select = "SELECT user.* from (
                    SELECT *, (
                        (
                            2 * 3960 * ATAN2(
                                SQRT(
                                    POWER(SIN((RADIANS(:latitude - latitude))/2), 2) +
                                    COS(RADIANS(latitude)) *
                                    COS(RADIANS(:latitude )) *
                                    POWER(SIN((RADIANS(:longitude - longitude))/2), 2)
                                ),
                                SQRT(1-(
                                    POWER(SIN((RADIANS(:latitude - latitude))/2), 2) +
                                    COS(RADIANS(latitude)) *
                                    COS(RADIANS(:latitude )) *
                                    POWER(SIN((RADIANS(:longitude- longitude))/2), 2)
                                )
                            )
                        )
                    ) * 1.609344
                ) AS distance
                FROM user
                WHERE longitude IS NOT NULL AND latitude IS NOT NULL
            ) AS user
                LEFT JOIN ofPrivacyList ON username = :unique_id
                LEFT JOIN user_hide_setting ON user_hide_setting.user_id = :user_id AND hide_user_id = user.id ";
            $where[] = 'distance <= :distance';
            $bind['distance'] = $criteria['distance'];
            $bind['longitude'] = $userDto->longitude;
            $bind['latitude'] = $userDto->latitude;
        }

        // Search user by emoticon_id
        if (!empty($criteria['emoticon_id'])) {
            $where[] = 'user.emoticon_id = :emoticon_id';
            $bind['emoticon_id'] = $criteria['emoticon_id'];
        }

        // Search user by avatar_id
        if (!empty($criteria['avatar_id'])) {
            $where[] = 'user.avatar_id = :avatar_id';
            $bind['avatar_id'] = $criteria['avatar_id'];
        }

        // Search user by status
        if (!empty($criteria['status'])) {
            $where[] = 'user.description LIKE :status';
            $bind['status'] = '%'.$criteria['status'].'%';
        }

        $select = $select . ' WHERE ( ' . implode(' ) AND ( ', $where) . ' ) ';

        if ( ! $order) {
            $order = 'user.last_access DESC';
        }
        $select .= ' ORDER BY ' . $order . ' ';
        if ($count) {
            if ( ! $offset) $offset = 0;
            $select .= " LIMIT {$offset}, {$count}";
        }

        $rows = $this->getAdapter()->fetchAssoc($select, $bind);
        $rowset = new Qsoft_Db_Table_Rowset(array('data' => array_values($rows), 'table' => $this, 'rowClass' => 'Dto_User'));
        if (empty($criteria['distance'])) {
            return $rowset;
        }

        // add distance to return data
        foreach ($rowset as $rowDto) {
            /* @var $rowDto Dto_User */
            $rowDto->addColumn('distance', $rows[$rowDto->id]['distance']);
        }

        return $rowset;
	}

	/**
     * Generate filter query to display in the user list
     *
     * @param   array 				$options
	 * @param	null|Zend_Db_Select	$select
     * @return  Zend_Db_Select
     */
	public function doFilter(array $criteria = array(), Zend_Db_Select $select = null)
	{
		if ( ! $select instanceof Zend_Db_Select) {
			$select = $this->select()
                      ->from($this->_name)
                      ->setIntegrityCheck(false)
                      ->joinLeft('avatar','user.avatar_id = avatar.avatar_id', array('avatar_id','avatar_img','sex','show_hide'))
                      ->joinLeft('feeling_img','user.emoticon_id = feeling_img.feeling_img_id', array('feeling_img_id','feeling_img','show_hide'));
                      //->joinLeft('user_age', 'user.age = user_age.id', array('age AS user_age'))
                      //->joinLeft('region', 'user.pref = region.id', array('region_name AS user_pref'));
                      ;

		}

        // Search user by id
		if (isset($criteria['id']) and strlen($criteria['id'])) {
			$select->where('user.id = ?', $criteria['id']);
		}

        // Search user by age
		//if (isset($criteria['age']) and strlen($criteria['age'])) {
		//	$select->where('user.age = ?', $criteria['age']);
		//}

        // Search user by pref (region)
		//if (isset($criteria['pref']) and strlen($criteria['pref'])) {
		//	$select->where('pref = ?', $criteria['pref']);
		//}

        // Search user by black list
		if (isset($criteria['black_list']) and strlen($criteria['black_list'])) {
			$select->where('black_list = ?', $criteria['black_list']);
		}

        // search by sex
        if (!empty($criteria['sex'])) {
            $select->where("avatar.sex IN (?)", $criteria['sex']);
        }

        // Search user by unique_id
		if (isset($criteria['unique_id']) AND strlen($criteria['unique_id'])) {
            $select->where("unique_id LIKE ?", "%{$criteria['unique_id']}%");
        }

        // Search user by user_id
		if (isset($criteria['user_id']) AND strlen($criteria['user_id'])) {
            $select->where("user_id LIKE ?", "%{$criteria['user_id']}%");
        }

        // Search user by nick_name
		if (isset($criteria['nick_name']) AND strlen($criteria['nick_name'])) {
            $select->where("nick_name LIKE ?", "%{$criteria['nick_name']}%");
        }

        // Search user by emoticon_id
        if (!empty($criteria['emoticon_id'])) {
            $select->where("user.emoticon_id IN (?)", $criteria['emoticon_id']);
        }

        // Search user by avatar_id
        if (!empty($criteria['avatar_id'])) {
            $select->where("user.avatar_id IN (?)", $criteria['avatar_id']);
        }

        // search by registration date
        if (isset($criteria['created_at_from']) AND strlen($criteria['created_at_from'])) {
            $select->where('DATE_FORMAT(user.created_at, \'%Y/%m/%d\') >= ?', $criteria['created_at_from']);
        }
        if (isset($criteria['created_at_to']) AND strlen($criteria['created_at_to'])) {
            $select->where('DATE_FORMAT(user.created_at, \'%Y/%m/%d\') <= ?', $criteria['created_at_to']);
        }

        // search by last access date
        if (isset($criteria['last_access_from']) AND strlen($criteria['last_access_from'])) {
            $select->where('DATE_FORMAT(user.last_access, \'%Y/%m/%d\') >= ?', $criteria['last_access_from']);
        }
        if (isset($criteria['last_access_to']) AND strlen($criteria['last_access_to'])) {
            $select->where('DATE_FORMAT(user.last_access, \'%Y/%m/%d\') <= ?', $criteria['last_access_to']);
        }

        // search by point
        if (isset($criteria['point_from']) AND strlen($criteria['point_from'])) {
            $select->where('user.point >= ?', $criteria['point_from']);
        }
        if (isset($criteria['point_to']) AND strlen($criteria['point_to'])) {
            $select->where('user.point <= ?', $criteria['point_to']);
        }

        // search by state
        if (!empty($criteria['state'])) {
            $select->where("state IN (?)", $criteria['state']);
        }

        // search by image display
        if (!empty($criteria['image_display'])) {
            $select->where("image_display IN (?)", $criteria['image_display']);
        }

        // search by valuation
        if (!empty($criteria['valuation'])) {
            $select->where("valuation IN (?)", $criteria['valuation']);
        }

        $select->order('id DESC');

		return $select;

	}

    /**
     * Generate filter query to display in the user image list
     *
     * @param   array               $options
     * @param   null|Zend_Db_Select $select
     * @return  Zend_Db_Select
     */
    public function doImageFilter(array $options = array(), Zend_Db_Select $select = null)
    {
        if ( ! $select instanceof Zend_Db_Select) {
            $select = $this->select()
                ->where('CHAR_LENGTH(image) > 0')
            ;
        }

        // Search user by user_id
        if (isset($criteria['user_id']) AND strlen($criteria['user_id'])) {
            $select->where("user_id LIKE ?", "%{$criteria['user_id']}%");
        }

        return $select;
    }

    /**
     * Return list pagination
     *
     * @param   int             $page
     * @param   int             $limit
     * @param   array|Zend_Db_Select  $select
     * @param   boolean         $fetchArray     Fetch the results item as array
     * @return  Zend_Paginator
     */
    public function getPagination($page = 1, $limit = null, $select = null, $fetchArray = false)
    {
        if (is_array($select)) {
            $select = $this->doFilter($select);
        } elseif (!$select instanceof Zend_Db_Select) {
            $select = $this->select();
        }

        return parent::getPagination($page, $limit, $select, $fetchArray);
    }

    /**
     * Return image list pagination
     *
     * @param   int             $page
     * @param   int             $limit
     * @param   array|Zend_Db_Select  $select
     * @param   boolean         $fetchArray     Fetch the results item as array
     * @return  Zend_Paginator
     */
    public function getImagePagination($page = 1, $limit = null, $select = null, $fetchArray = false)
    {
        if (is_array($select)) {
            $select = $this->doImageFilter($select);
        } elseif (!$select instanceof Zend_Db_Select) {
            $select = $this->select();
        }

        return parent::getPagination($page, $limit, $select, $fetchArray);
    }

    /**
     * Mark all images as deleted
     *
     * @param mixed $idArray
     * @return void
     */
    public function dontDisplayImage($idArray = array())
    {
        if (!is_array($idArray)) {
            $idArray = array($idArray);
        }

        if ($idArray) {
            $where = $this->getAdapter()->quoteInto('id IN (?)', $idArray);
            $this->getAdapter()->update($this->_name, array('profile_user_img_id' => NULL), $where);
        }
    }
    public function fetchOneWithAvatar($name, $value = '', Zend_Db_Select $select = null)
    {
        if ( ! $select instanceof Zend_Db_Select) {
             $field_select = array(
                       "sum(if(point_log.point > point_after,point_log.point-point_after,0)) as sum_purchase",
                       );
             $select = $this->select()
                      ->from($this->_name)
                      ->setIntegrityCheck(false)
                      ->joinLeft('avatar', 'user.avatar_id = avatar.avatar_id', array('avatar_id','avatar_img','sex','show_hide'))
                      ->joinLeft('point_log', 'user.id = point_log.user_id', $field_select)
                      ->group('user.id')
                      ;
        }

        if ( ! is_array($name)) {
            $name = array($name => $value);
        }

        foreach ($name as $field => $value) {
            $select->where($field . ' = ?', $value);
        }

        return $this->fetchRow($select);
    }

}
