<?php
/*
ini_set('error_reporting', 0);
ini_set('display_errors', false);
*/
//$a = time() + microtime();

define('bootstrap3', 1);

defined('PUBLIC_PATH')
|| define('PUBLIC_PATH', realpath(dirname(__FILE__)));

// Define path to application directory
defined('SAND_ROOT')
|| define('SAND_ROOT', realpath(dirname(__FILE__) . '/../../sand-core'));

require_once(SAND_ROOT . '/library/init.php');
require_once(SAND_ROOT . '/library/common.php');

//redirect www.vieted.com to vieted.com
$host = $_SERVER['HTTP_HOST'];

if (strpos($host, 'www.') === 0)
{
    $redirectedhost = substr($host, 4);//, $start)('www.', '', $host, 1);
    $r = "http://$redirectedhost" . $_SERVER['REQUEST_URI'];
    //die($redirected);
    header("Location: $r");
    die();
}

function is_root_by_checking_cookie()
{
    if (get_cookie('is_admin', '') == '1')
        return true;
    else 
        return false;
}

$ignore = false;
    
    
if(!$ignore){

	/** Zend_Application */
	require_once 'Zend/Application.php';

	// Create application, bootstrap, and run
	$application = new Zend_Application(
			APPLICATION_ENV,
			APPLICATION_PATH . '/configs/application.ini'
	);
	
    $application->bootstrap()
	    ->run();
}