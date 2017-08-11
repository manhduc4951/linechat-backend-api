<?php

define('ERROR_FORM_VALIDATION_FAILURE', '001');
define('ERROR_UNIQUE_ID_DUPLICATE', '002');
define('ERROR_USER_ID_DUPLICATE', '003');

define('ERROR_CHANGE_DEVICE_CODE_INVALID', '010');
define('ERROR_CHANGE_DEVICE_CODE_EXPIRED', '011');

define('ERROR_USER_NOT_FOUND', '020');
define('ERROR_GROUP_NOT_FOUND', '021');
define('ERROR_LIFELOG_NOT_FOUND', '022');
define('ERROR_LIFELOG_COMMENT_NOT_FOUND', '023');
define('ERROR_LOOK_UP_NO_RESULT', '024');
define('ERROR_LIFELOG_LIKE_TWICE', '025');
define('ERROR_USER_ID_CHANGE_TWICE', '026');

define('ERROR_CONNECTION_CHAT_SERVER', '030');
define('ERROR_UNKNOWN_FROM_CHAT_SERVER', '031');
define('ERROR_AUTH_FAILURE_CHAT_SERVER', '032');

define('ERROR_UPLOAD_FILE_INVALID', '040');
define('ERROR_CANNOT_RECEIVE_FILE', '041');
define('ERROR_UPLOAD_FILE_NOT_FOUND', '042');
define('ERROR_ZIP_FILE_IS_EMPTY', '043');

define('ERROR_USER_ALREADY_MEMBER_OF_GROUP', '050');
define('ERROR_USER_ALREADY_DELETED', '051');
define('ERROR_ITEM_EXPIRED', '052');

define('ERROR_AUTHENTICATE_FAILURE', '080');
define('ERROR_AUTHORIZE_DENY', '081');

define('ERROR_BAD_REQUEST', '400');
define('ERROR_ACCESS_DENIED', '401');
define('ERROR_FORBIDDEN', '403');
define('ERROR_NOT_FOUND', '404');
define('ERROR_APPLICATION_ERROR', '500');


//shop
define('ERROR_SHOP_SEND_GIFT', '090');
define('ERROR_SHOP_DISCARD_GIFT', '091');
define('ERROR_SHOP_NOT_FOUNT_STAMP', '092');
define('ERROR_SHOP_NOT_FOUNT_ITEM', '093');
define('ERROR_SHOP_NOT_FOUNT_GIFT', '094');
define('ERROR_SHOP_SPEND_POINT', '095');
define('ERROR_SHOP_ADD_POINT', '096');
define('ERROR_SHOP_SET_ITEM', '097');
define('ERROR_SHOP_SET_GIFT', '098');
define('ERROR_SHOP_SET_STAMP', '099');

define('ERROR_SHOP_SHOWED_GIFT', '100');
define('ERROR_SHOP_RECEIVED_GIFT', '100');
define('ERROR_GIFT_ID_INVALID', '112');
define('ERROR_PICOLLECTION_NOT_FOUND', '113');
//talk&shake
define('ERROR_TALKSHAKE_VALUATION', '102');
define('ERROR_TALKSHAKE_REPORT', '103');
define('ERROR_TALKSHAKE_START_SHAKE', '104');
define('ERROR_TALKSHAKE_END_SHAKE', '105');
define('ERROR_TALKSHAKE_START_NOW', '106');
define('ERROR_TALKSHAKE_END_NOW', '107');
define('ERROR_TALKSHAKE_MACHING_START', '108');
define('ERROR_TALKSHAKE_MACHING_END', '109');
define('ERROR_NOW_LOG_ID_INVALID', '110');
define('ERROR_SHAKE_LOG_ID_INVALID', '111');

//

// All errors message to explain the error code
return array(
    '001'   => 'The post parameters does not satisfy the form validate rules',
    '002'   => 'The unique id is already used',
    '003'   => 'The user id is already used',

    '010'   => 'Your changed code is invalid, please try again',
    '011'   => 'Your changed code was expired, please generate a new one',

    '020'   => 'Cannot found the user that you request',
    '021'   => 'Cannot found the group that you request',
    ERROR_LIFELOG_NOT_FOUND => 'Cannot found the lifelog that you request',
    ERROR_LIFELOG_COMMENT_NOT_FOUND => 'Cannot found the lifelog comment that you request',
    ERROR_LOOK_UP_NO_RESULT => 'Look up no result',
    ERROR_LIFELOG_LIKE_TWICE => 'You cannot like a like log twice',
    ERROR_USER_ID_CHANGE_TWICE => 'You can change the user id one time only',
    
    ERROR_CONNECTION_CHAT_SERVER    => 'Cannot connect to openfire server',
    ERROR_UNKNOWN_FROM_CHAT_SERVER  => 'Receive unknown error from openfire server',
    ERROR_AUTH_FAILURE_CHAT_SERVER  => 'Cannot login to openfire server',
    
    ERROR_UPLOAD_FILE_INVALID       => 'The file does not satisfy the validate rules',
    ERROR_CANNOT_RECEIVE_FILE       => 'Cannot receive file',
    ERROR_UPLOAD_FILE_NOT_FOUND     => 'Cannot found the request file',
    ERROR_ZIP_FILE_IS_EMPTY         => 'Zip file is empty',
    
    ERROR_USER_ALREADY_MEMBER_OF_GROUP  => 'User already a member of group',
    ERROR_USER_ALREADY_DELETED => 'Cannot perform this task because user already deleted',
    ERROR_ITEM_EXPIRED => 'This item is expired or you have not bought it already',

    '080'   => 'Cannot login with your identity',
    '081'   => 'You does not have permission to acess this item',

    '400'   => 'Bad request',
    '401'   => 'Access denied',
    '403'   => 'Forbidden',
    '404'   => 'Error not found',
    '500'   => 'Application error',


	ERROR_SHOP_SEND_GIFT  => 'Cannot send gift',
	ERROR_SHOP_DISCARD_GIFT => 'Cannot discard gift',
	ERROR_SHOP_NOT_FOUNT_STAMP => 'Not fount stamp',
	ERROR_SHOP_NOT_FOUNT_ITEM => 'Not fount item',
	ERROR_SHOP_NOT_FOUNT_GIFT => 'Not fount gift',
	ERROR_SHOP_SPEND_POINT => 'Cannot spend point',
	ERROR_SHOP_ADD_POINT => 'Cannot add point',
	ERROR_SHOP_SET_ITEM => 'Cannot set item',
	ERROR_SHOP_SET_GIFT => 'Cannot set gift',
	ERROR_SHOP_SET_STAMP => 'Cannot set stamp',
	ERROR_SHOP_SHOWED_GIFT => 'Cannot showed gift',
	ERROR_SHOP_RECEIVED_GIFT => 'Cannot received gift',
	ERROR_NOW_LOG_ID_INVALID => 'now_log_id invalid',
	ERROR_SHAKE_LOG_ID_INVALID => 'shake_log_id invalid',
	ERROR_GIFT_ID_INVALID => 'gift_id invalid',
	ERROR_PICOLLECTION_NOT_FOUND => 'Not found picollection',

);