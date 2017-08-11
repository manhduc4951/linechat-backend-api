<?php

/**
 * Dao_Shake
 * 
 * @package Dao
 * @author duyld
 */
class Dao_Shake extends Qsoft_Db_Table_Abstract implements Dao_Interface_EntityAccessable
{

    protected $_name = 'shake_data';
    
    protected $_rowClass = 'Dto_Shake';
    
    /**
     * Seach all data that not expired and in distance
     * 
     * @param Dto_Shake $shakeDto
     * @param Dto_User $userDto
     * @param float $distance
     * @return Qsoft_Db_Table_Rowset
     */
    public function inExpiredTimeAndDistance(Dto_Shake $shakeDto, Dto_User $userDto, $distance)
    {
    	// filter rules:
    	//  - the position must be in provided distance ranger
    	//  - if the shake is completed before we start to shake, do ignore,
    	// if it completed after we start, collect it
    	// - Users must not be friend together, that means roster with to and both state is not allowed
    	// - Changed: user can be friend, but return the status
        $result = $this->getAdapter()->fetchAssoc("
                SELECT *
                FROM (
                    SELECT shake_data.*, ofRoster.sub, (
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
                FROM shake_data
                    LEFT JOIN
                        ofRoster ON
                            ofRoster.username = :unique_id AND
                            ofRoster.jid = shake_data.jid
                WHERE
                    id != :shake_id AND
                    completed_at > :created_at
                ) as tmp_table
                WHERE
                    distance < :distance
                ORDER BY distance asc
            ", array(
//                       AND
//                     (
//                         sub IS NULL OR
//                         sub = :sub_none OR
//                         sub = :sub_from
//                     )
                'unique_id' => $userDto->unique_id,
                'distance' => $distance,
                'latitude' => $shakeDto->latitude,
                'longitude' => $shakeDto->longitude,
                'shake_id' => $shakeDto->id,
                'created_at' => $shakeDto->created_at,
                //'sub_none' => self::ROSTER_SUBSCRIPTION_NONE,
                //'sub_from' => self::ROSTER_SUBSCRIPTION_FROM,
            )
        );
        
        // convert result array to rowset
        $result = array_values($result);
        $rowset = new $this->_rowsetClass(array('data' => $result, 'rowClass' => 'Dto_Shake'));

        return $rowset;
    }
    
}