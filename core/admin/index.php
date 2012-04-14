<?php

/*-------------------------------------------------------*\
|  PHP BasicFramework - Ready to use PHP site framework   |
|  License: LGPL, see LICENSE                             |
\*-------------------------------------------------------*/

/**
* This is the index of the administration console
* @file index.php
* @package PHPBasicFramework
* @version 0.6
* @author L. Minghetti
* @date Started on the 2008-07-02
*/


/*   SET OPTIONS   */
error_reporting(E_ALL ^ E_NOTICE);

/*   SESSION    */
@session_start();

/*   LOAD ENV CONFIG   */
include_once ("configuration.php");

/*   LOAD COMMON   */
include_once ("include/class.common.php");
common::load_class("file");
common::load_class("config");
common::load_class("test");


/*   CHECK LOGGED  */
common::check_logged();

/*   ACTIONS    */

if (isset($_GET['edit'])) {
	$edit = $_GET['edit'];
	common::load_edit($edit);
	
} else {
	$view = isset($_GET['view'])? $_GET['view'] : 'about';
	common::load_view($view);
}

