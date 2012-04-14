<?php

/*-------------------------------------------------------*\
|  PHP BasicFramework - Ready to use PHP site framework   |
|  License: LGPL, see LICENSE                             |
\*-------------------------------------------------------*/

/**
* simple password module
* @file BF_simple_password.php
* @package PhpBF
* @subpackage simple_password
* @version 0.1
* @author Loic Minghetti
* @date Started on the 2012-04-09
*/

// Security
if (!defined('C_SECURITY')) exit;


class BF_simple_password {
	
	static public function check ($password) {
		if (isset($_SESSION["_simple_password_md5"]) && $_SESSION["_simple_password_md5"] == md5($password)) return true;
		
		if (isset($_POST["_simple_password"]) && md5($_POST["_simple_password"]) == md5($password)) {
			$_SESSION["_simple_password_md5"] = md5($_POST["_simple_password"]);
			return true;
		}
		
		BF::load_module("BF_output_template");
		$tpl = new BF_output_template("simple_password");
		$tpl->disp();
		die();
	}
}
