<?php

// Liberty Tech Framework - v2.3
// Initialise page
// ---

include 'functions/functions.php';

// Global config

$site_url = ($_SERVER['SERVER_PORT'] == 443 ? 'https' : 'http') . '://' . strtolower($_SERVER['HTTP_HOST']);
date_default_timezone_set('Europe/London');
setlocale(LC_MONETARY, 'en_GB');
define('CHARSET', 'utf-8');
header('Content-Type: text/html; charset=UTF-8');
header('X-Frame-Options: SAMEORIGIN');
set_time_limit(5);

define('SITE_URL', strtolower(arp($_SERVER, 'HTTP_HOST', exec('hostname -f'))));
define('HTML_ROOT', dirname(dirname(__FILE__)));
define('SITE_ROOT', dirname(HTML_ROOT));
define('WP_SITEURL', 'http://evelynjoseph.co.uk');
define('WP_HOME', 'http://evelynjoseph.co.uk');

// Session settings

$sid = arp($_COOKIE, 'id');
if ($sid && !preg_match('/^[a-zA-Z0-9,\-]{22,40}$/', $sid)) unset($_COOKIE['id']);

session_name('id');
$currentCookieParams = session_get_cookie_params(); 
session_set_cookie_params(
	$currentCookieParams['lifetime'],
	$currentCookieParams['path'],
	$currentCookieParams['domain'],
	$currentCookieParams['secure'],
	true
);
session_start();


// URL parsing

($url = parse_url($_SERVER['REQUEST_URI'])) && ($url = rtrim($url['path'], '/') or $url = '/home');
$arrURL = array_pad(explode('/', urldecode($url)), 8, '');
foreach ($arrURL as &$v) $v = preg_replace('/[^a-z0-9\-\(\)_\.\&]/', '', strtolower($v)); unset($v);

$arrDomain = explode('.', $_SERVER['SERVER_NAME']);
$arrURL[0] = (count($arrDomain) > 2) ? $arrDomain[count($arrDomain) - 3] : '';

// die(print_r($arrURL));

// print_r($arrURL);
// die();

// Post settings

if (get_magic_quotes_gpc())
{
	function remslashes_deep($value) { return is_array($value) ? array_map('remslashes_deep', $value) : stripslashes(trim($value)); }
	$gs = array(&$_GET, &$_POST, &$_FILES, &$_COOKIE, &$_REQUEST);
	foreach ($gs as &$g) $g = array_map('remslashes_deep', $g); unset($g); unset($gs);
}

$allowed_ip = array('84.18.210.114', '84.18.210.116');

if ($arrURL[1] != 'calls' && $arrURL[2] != 'paypal')
	if (!empty($_POST) && !in_array($_SERVER['REMOTE_ADDR'], $allowed_ip) && substr(strtolower($_SERVER['HTTP_REFERER']), 0, strlen($site_url)) != $site_url)
		die('Security error #323 ' . $_SERVER['REMOTE_ADDR']);

// Include classes

ob_start();
include 'classes/_liberty.database.php';
include 'classes/_liberty.error.php';
include 'classes/_liberty.structure.php';
include 'config/config.php';
ob_end_clean();

// Run classes

$error = new Error($developer_email);

$db = new DB($db_settings['host']);
$db->setLogin($db_settings['user'], $db_settings['pass'])->setDB($db_settings['name']);

$page = new Page();
