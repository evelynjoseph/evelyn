<?php

include 'config/initialise.php';

if ($arrURL[1] == 'cron')
	return require 'cron/index.php';

if ($arrURL[1] == 'calls')
	return require 'calls/index.php';

$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : 0;

$user = $db->sql("SELECT * FROM users WHERE user_id = %d", $user_id)->row();

$admin = isset($_SESSION['admin']);

if (!$user && !$admin)
{
	$public_pages = array('home', 'about', 'social', 'contact', 'login');
	if (!in_array($arrURL[1], $public_pages)) redirect('/login?return=' . urlencode(substr($_SERVER['REQUEST_URI'], 1)));
}

// Initialise page

$page	->setTitle('Evelyn Joseph - Web Developer')
		->addCSS('font-awesome.css')
		->addCSS('font-awesome.min.css')
		->addCSS('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css')
		->addCSS('styles.css');

$page	->addJS('http://ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js')
		->addJS('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js')
		->addJS('_liberty.structure.js');

// Load page
		
is_dir($path = 'pages/' . $arrURL[1]) && is_file($path . '/index.php') && ~+(require $path . '/index.php') or	# pages/xx/index.php
is_file($path . '.php') && ~+(require $path . '.php') or require 'pages/404.php';				# pages/xx.php

// Load page

// if (!LIVE) $page->printDebug();
$page->setHeader();
$page->setFooter();
