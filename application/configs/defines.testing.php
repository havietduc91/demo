<?php
/**
 * SITE specifics
 */
//define('POST_PAGE_SIZE', 30);
define('SITE_URL', 'http://vieted.com');
//define('SITE_URL', 'http://www.oakq.com');
define('ASSETS_CDN', 'http://bao30.com/');
define('DESIGN_PATH', 'http://stuffcdn.local/design/cg/');
define('NOREPLY_EMAIL', 'task@eekip.com');
define('DOMAIN', 'vieted.com');
define('CONTACT_EMAIL', 'hoibinet@gmail.com');
define('USE_AMAZON_SES', false); // true => send mail with amazon SES
define('FILES_UPLOAD_PATH', realpath(APPLICATION_PATH . "/../") . "/hidden/");
define('JSVERSION', '1340447153');
define('PAGE_RANGES', 7);//numbers pagination links at a time

/**
 * DB connections
 */
//include_once('functions.php');
define('MONGO_HOST', 'localhost');
//define('DICT_BACKEND', 'mongo');
define('DICT_BACKEND', 'redis');
define('REDIS_PORT', 6379);
define('REDIS_HOST','127.0.0.1');
define('REDIS_PASS', '');

define('RDB_DICT_DB', 30); //[0,1,2] is kept for eekip, [3,4,5] for edx,, [6,7,8] for taxi
define('RDB_CACHE_DB', 31);
define('RDB_QUEUE_DB', 32);

define('RDB_DICT_PREFIX', 'd:'); //dict

define('DICT_RDB_USER_PREFIX', 'u:');
define('DICT_RDB_VOCABULARY', 'vocabulary'); // set of all words in the dict

/**
 * QUEUE
 */
//queue
define('RDB_QUEUE_PREFIX', 'resque:'); // prefix for all queue-related keys
define('QUEUE_ADMIN_PASS', '123');


/** Paginations & limits */
define('COMMENT_PAGE_SIZE',20);
define('POST_PAGE_SIZE',20);
define('ACTIVITY_PAGE_SIZE',5);
define('MAXIMUM_TAGS_PER_POST', 10);
define('MAXIMUM_USERS_PER_POST', 5);

define('FOLLOW_LIMIT', 10);
define('USER_ITEM_PER_PAGE', 10);
define('USER_FRIEND_CACHE_LIMIT', 10);
define('USER_NOTIF_CACHE', 10);///only cache 20 notifications
define('USER_NOTIF_READ_CACHE', 10);
define('USER_ITEM_CACHE_LIMIT', 10);
define('COMMENT_CACHE_IN_POST_LIMIT', 3);
define('GALLERY_ITEM_LIMIT', 50);
define('USER_FOLLOW_USER_LIMIT', 10);
define('USER_FOLLOW_ITEM_LIMIT', 10);
define('ASSOCIATE_LIMIT', 100);
define('OWN_LOWER_LIMIT', 2); // own = OWN_LOWER_LIMIT is ok
define('TEXT_POST_LIMIT', 300); //when to trim/breadcumb post content to "more.."
define('CACHED_POST_TITLE_WORD_LENGTH', 20); //number of words
define('CACHED_COMMENT_TITLE_WORD_LENGTH', 20); //number of words

/**
 * File upload & path
 */
define('DEFAULT_AVATAR_URL','http://d17yofrdipd1db.cloudfront.net/images/avatar.gif');
define('SYSTEM_AVATAR_URL', 'http://d17yofrdipd1db.cloudfront.net/images/warning.jpg');
//define('AVATAR_PREFIX', 'http://d3syq05o3krv6a.cloudfront.net');
define('AVATAR_PREFIX', '/ufiles');
// directory structure to upload avatar
define('AS3_BUCKET_NAME', 'stuffcdn');
define('IMG_STORE_PATH', '/ufiles/');
define('IMG_STORE_LOCAL', true); //true => store local || false => upload to AmazonS3
define('SET_UPLOAD_ITEM_AVATAR_NOW', false); // set upload avatar together insert items?
define("DEFAULT_ITEM_AVATAR_SIZE", 50);// avatar size to insert DB
define("DEFAULT_USER_AVATAR_SIZE", 50);// avatar size to insert DB
define('AWS_KEY', 'AKIAIQRQ3ZPTGZRYDY2Q');
define('AWS_SECRET', '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');
define('AS3_AVATAR_FOLDER', 'avatar');
define('AS3_ITEM_IMAGE_FOLDER', 'image');// 'item');
define('SECURE_FILES_UPLOAD_PATH', realpath(APPLICATION_PATH . "/../") . "/hidden");
define('PUBLIC_FILES_UPLOAD_PATH', realpath(APPLICATION_PATH . "/../") . "/public/ufiles");
define('SECURE_FILES_SERVER', 'local');
define('PUBLIC_FILES_SERVER', 'local');

function default_avatar($nodeType)
{
	return ASSETS_CDN . "/images/avatar.gif";
}

/**Misc*/
define('GOOGLE_USER_IP', '117.5.182.45');
define('UPLOAD_AVATAR_LOG_TO_FILE', true);//log upload avatar?
define('TOKEN_PERSISTENT_LIMIT', 10); // limited number user token persistent.
//task
define('DATE_DELIMITER', '/');
define('TICKER_INTERVAL', 10000);
define('MAX_TIME_STAMP', 'm'); // .


/**
 * Framework specifics
 */
define('HASH_ALGO', 'md5'); //password hash algorithm. Can be sha1
defined ('COOKIE_PREFIX') || define('COOKIE_PREFIX', '_cl_');
defined ('COOKIE_SALT') || define('COOKIE_SALT', 'dkmmhehehe'); //salt for _cl_uhash
define('GUEST_ID', 0);
defined ('COOKIE_SESSION_TIMEOUT') || define('COOKIE_SESSION_TIMEOUT', 60 * 60 * 24); //seconds . Logically must be > PHP session period
define('CL_NONE_SEARCH_KEY', 'cl_no_search'); //_cl_no_search?
define('NESTED_DOCUMENT_SEP', '__');


/**
 * Testing env
 */
define('TEST_PASS', 'c6ce2c4df6a9b93cac7aec85cb4aa380');
