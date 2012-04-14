<?php

/**
 * Path to Admion console root
 */
define ("DIR", dirname(__FILE__));

/**
 * Configuration file of the admin console
 * Name must be placed in Admin/configuration.php
 */


/**
 * Relative or absolute path to website root for reading (must end with '/')
 */
define("PATH_TO_ROOT", "../../");

/**
 * Relative or absolute path to website root for writing (must end with '/')
 * Use an FTP url here to edit files even if web server does not have write permission.
 * Note: This will only work with folders having a relative path to web site root. Absolute paths may not be accessed using ftp
 */
define("PATH_TO_ROOT_WRITE", "../../");


/**
 * Path to config file
 * Config file must be named config.php and be placed next to Framework.php
 */
define("CONFIG_FILE", "../config.php");


/**
 * Path to config file for writing
 * Use an FTP url here to edit even if web server does not have write permission.
 */
define("CONFIG_FILE_WRITE", "../config.php");



