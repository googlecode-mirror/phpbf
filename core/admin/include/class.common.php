<?php


class common {
	static function error ($message) {
		die($message);
	}
	static function flush ($message = '') {
		print $message;
		@ob_flush();
		flush();
	}
	static function get_version () {
		if (file::get_folder_path('framework') === false) return;
		return @file_get_contents(get_file('framework', 'VERSION')->get_path());
	}
	static function url($location) {
		switch ($location) {
			case 'online' : return ONLINE_RESSOURCES_URL;
			case 'self' : return "./";
			case 'website' : return "http://www.phpbf.net/";
			case 'sourceforge' : return "http://sourceforge.net/projects/phpbf/";
		}
	}
	static function load_view($view) {
		common::print_header($view);
		include (DIR."/include/view.".$view.".php");
		common::print_footer();
	}
	static function load_edit($edit) {
		include (DIR."/include/edit.".$edit.".php");
	}
	static function load_class($class) {
		include_once (DIR."/include/class.".$class.".php");
	}
	static function check_logged() {
		if (!file_exists(DIR."/.htpasswd")) die("Please create .htpasswd file first before accessing the Admin Console. In Linux, run this command in document root:<br/>$ htpasswd -c ".DIR."/.htpasswd USERNAME");
		$auth = false;
		
		// AMA - 04/12/2010 - enabling fonctionality in 1and1 - Begin
		if ( (!isset($_SERVER['PHP_AUTH_USER']) || !$_SERVER['PHP_AUTH_USER']) 
			&& isset($_SERVER['REDIRECT_REMOTE_USER']) 
			&& preg_match('/Basic\s+(.*)$/i', $_SERVER['REDIRECT_REMOTE_USER'], $matches)) {

			list($name, $password) = explode(':', base64_decode($matches[1]));
			$_SERVER['PHP_AUTH_USER'] = strip_tags($name);
			$_SERVER['PHP_AUTH_PW'] = strip_tags($password);
        }
		// AMA - End
		if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
			$user = $_SERVER['PHP_AUTH_USER'];
			$pass = $_SERVER['PHP_AUTH_PW'];
			$entries = file(DIR."/.htpasswd");
			foreach ($entries as $entry) {
				list($entry_user, $entry_pass) = explode(":", trim($entry));
				if ($entry_user == $user) {
					$salt = substr($entry_pass,0,2);
					$test = crypt($pass, $salt);
					if($test == $entry_pass) {
						$auth = true;
						break;
					}
				}
			}
		}
		if ($auth !== true) {
			header('WWW-Authenticate: Basic realm="Admin Console Authentication"');
			header('HTTP/1.0 401 Unauthorized');
			die('Authentication is required to view this page.'); 
		} else {
			define("LOGGED", true);
		}
	}
	static function print_header ($view) {
		header("Content-Type: text/html; charset=UTF-8");
		ob_start();
		print '<!doctype html public "-//w3c//dtd html 4.0 transitional//en"><html><head>
		<meta http-equiv=content-type content="text/html; charset=UTF-8">
		<title>'.common::get_version().' - Administrative console</title>
		<link rel="shortcut icon" href="./favicon.ico">
		<style>
		html, body {
			background-color: #FFFFFF;
			height: 100%; margin: 0;
		}
		body, td, span, input, textarea {
			font-family: arial,helvetica,clean,sans-serif;
			font-size:12px;
		}
		div.header {
			width: 100%; height: 60px;
			text-align: center;
			background-color: #DDDDDD;
			padding: 20px 0px 0px 0px;
			color: #006699;
			font-family : arial, "lucida console", sans-serif;
			font-size: 25px;
		}
		div.menu_bg {
			width: 100%; height: 53px;
			position: absolute; top: 80px; left: 0;
			background:#3082AD url('.common::url('online').'Images/bg.jpg) repeat-x scroll 0 0;
		}
		div.menu {
			background-image:url('.common::url('online').'Images/menu.jpg);
			background-repeat:no-repeat;
			height:53px; width:800px;
			position: relative; top: 0; left: 0;
			line-height:53px;
			margin:0 auto -1px;
			text-align:center; vertical-align:middle;
		}
		div.footer {
			background-color:#DDDDDD;
			margin-top:30px;
			padding: 3px 0 3px 0;
			text-align:center;
			width:100%;
			float: left;
		}
		div.content {
			margin:0 auto -1px;
			position:relative;
			width:800px;
			padding: 20px 0;
			font-size: 14px;
		}
		div.menu a:hover {
			color:#E8F1F6;
			text-decoration: underline;
		}
		div.menu a.selected, a.selected:hover {
			color:#3082AD;
			text-decoration: none;
			background: #E7F0F5 url('.common::url('online').'Images/button_bg.jpg) repeat-x scroll 0 0;
		}
		div.menu a {
			color:#FFFFFF;
			font-size:130%;
			font-weight:bold;
			height:100%;
			margin:0 10px;
			vertical-align:middle;
			padding: 5px;
		}
		hr {
			border: 0;
			width: 100%;
			height: 1px;
			padding: 0px;
			margin: 0px;
			background-color: #CCCCCC;
		}
		a, a:link, a:active, a:visited {
			color: #000066;
			cursor: pointer;
			text-decoration: none;
		}
		a:hover {
			color: blue;
			cursor: pointer;
			text-decoration: none;
		}
		h2 {
			border-bottom: 1px solid #CCCCCC;
		}
		h3 {
			margin-bottom: 5px;
		}
		
		fieldset {
			margin: 5px;
			background-color: #F3F3F3;
			border: solid 1px #CCCCCC;
			padding: 10px;
			padding-left: 15px;
			margin-bottom: 30px;
		}
		fieldset table {
			width: 100%;
		}
		legend {
			margin-left: -10px;
			font-weight: bold;
		}
		td.name {
			text-align: left;
			width: 150px;
			padding-right: 10px;
			color: red;
			vertical-align: top;
		}
		td.name input {
			width: 70px;
			color: red;
		}
		td.field, td.field_checkbox {
			width: 350px;
		}
		td.field input, td.field_table input {
			width: 100%;
		}
		input {
			border: solid 1px #CCCCCC;
		}
		td.help {
			font-size: small;
			padding-bottom: 5px;
		}
		td.default {
			padding-left: 10px;
			font-size: small;
			font-style: italic;
		}
		td {
			vertical-align: top;
		}
		</style>
		</head>
		<body>
		<div class="header">
			<img alt="PhpBF" src="'.common::url('online').'Images/logo.png" style="margin-right: 50px; vertical-align: middle;">
			Administrative console
		</div>
		<div class="menu_bg"></div>
		<div class="menu">
			<a '.($view == 'config' || $view == 'config_saved' || $view == 'reset_confirm'? 'class="selected"':'').'href="'.common::url('self').'?view=config">Configuration</a>
			<a '.($view == 'test'? 'class="selected"':'').'href="'.common::url('self').'?view=test">Test server</a>
			<a '.($view == 'backup'? 'class="selected"':'').'href="'.common::url('self').'?view=backup">Backup</a>
			<a '.($view == 'log'? 'class="selected"':'').'href="'.common::url('self').'?view=log">Error log</a>
			<a '.($view == 'about' || $view == 'license'? 'class="selected"':'').'href="'.common::url('self').'?view=about">About</a>
		</div>
		<div class="content">
		';
	}
	static function print_footer () {
		print '
		</div>
		<div class="footer">
			'.common::get_version().' | <a href="'.common::url('website').'">www.phpBF.net</a>
		</div>
		</body></html>
		';
		ob_flush();
	}
}


