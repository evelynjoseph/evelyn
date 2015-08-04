<?php

global $db, $user, $admin, $site, $page;

$head = <<<HEAD
<!doctype html> 
<html lang="en"> 
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset="utf-8">
	<meta name="robots" content="noindex,nofollow">
	<link rel="shortcut icon" href="favicon.ico" /> 
	$tags
</head>

<body class="$class">

<nav class="navbar navbar-inverse navbar-fixed-top">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="/">Evelyn Joseph</a>
		</div>
		<div id="navbar" class="collapse navbar-collapse">
			<ul class="nav navbar-nav">
				<li><a href="/about">About</a></li>
				<li><a href="/contact">Contact</a></li>
			</ul>
		</div>    
	</div>
</nav>
				
HEAD;


return $head;

	// <form class="navbar-form navbar-right">
	// 			<div class="form-group">
	// 				<input type="text" placeholder="Email" class="form-control">
	// 			</div>
	// 			<div class="form-group">
	// 				<input type="password" placeholder="Password" class="form-control">
	// 			</div>
	// 			<button type="submit" class="btn btn-success">Sign in</button>
	// 		</form> 