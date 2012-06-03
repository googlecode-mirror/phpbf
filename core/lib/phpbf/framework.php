<?php

/*-------------------------------------------------------*\
|  PHP BasicFramework - Ready to use PHP site framework   |
|                                                         |
|  Set of files to use as a common site structure.        |
|  Making database access, templates and design easier,   |
|  based upon the smarty temlate engine.                  |
|                                                         |
|  Copyright (C) 2004 - 2007  Loic Minghetti              |
|                                                         |
|  License: LGPL, see LICENSE                             |
|  Version : 0.7                                          |
\*-------------------------------------------------------*/

/**
 * This is the main script of the framework
 * @file framework.php
 * @package PhpBF
 * @version 0.4
 * @author L. Minghetti
 * @date Started on the 2006-04-25
*/

// Security
	/// used to make sure php scripts and modules are only opened from the framework
	define("C_SECURITY", TRUE);

// Start time counter
	/// used to time the execution of the script. Comment out when not used
	//$_time_start = (float) array_sum(explode(' ', microtime()));

// Define path to config file
define("BF_RELATIVE_PATH_TO_CONFIG", "../../config.php");

/**
 * Main static class
 
 * @package PhpBF
 */

class BF {
	
	/**
	 * @var 	array	$conf : Stores all config entries.
	 * 					It may be modified, and modifications will be available during script execution. To add an entry : BF::$conf['newentry'] = 'blabla';
	 * @warning	Init the framework before editing this entry
	 */
	public static $conf = array();
	/**
	 * @var 	int 	$time : Timestamp when framework was initiated
	 */
	public static $time;
	
	public static $path_to_framework;
	public static $path_to_root;
	/**
	 * @var 	String	$base_url : Relative or absolute URL to phpBF root on browser side. Must end with '/'. Absolute is relative to document root and must starts with '/'.
	 * @warning	Reset BF::$folders array after editing 
	 */
	public static $base_url;

	public static $path_info_array = array();
	public static $path_info = '';
	
	private static $folders = array();
	
	public static $encoding = 'UTF-8';
	
	/**
	 * @var 	callback	$error_output_callback : callback toi display error message as registered by output module
	 * @warning	Reset BF::$folders array after editing 
	 */
	public static $error_output_callback = null;
	
	/**
	 * Method to init the framework
	 * @param	string	$access [optional default null] : Restrict access with an access string. See manual for syntax. Eg. 4|-testgroup|+u:testuser|-uid:4|+gid:20
	 * @param	string	$section [optional default null] : Set a section ID
	 */
	public static function init($access = null, $section = null) {
		
		/**
		 * Timestamp
		 */
		BF::$time = time();

		/**
		 * Absolute path to framework
		 */
		BF::$path_to_framework = strtr(dirname(__FILE__), "\\", "/");
		if (BF::$path_to_framework[strlen(BF::$path_to_framework)-1] != '/') BF::$path_to_framework .= '/';
		
		/**
		 * CONFIGURATION
		 */
		$_conf =& BF::$conf;
		if ( (@include (BF::$path_to_framework.BF_RELATIVE_PATH_TO_CONFIG)) === false)  die('<h3>Framework failed loading config file.</h3>Please inform webmaster<br/><br/>Webmaster, make sure <i>config.php</i> exists in the <i>Framework</i> folder and has the right permissions set. Otherwise use <i>Admin Console</i> to regenerate. Refer to doc for more info.');


		/**
		 * Absolute path to website root (server side)
		 */
		BF::$path_to_root = substr(BF::gc('path_to_root'), 0, 1) == '/'? BF::gc('path_to_root') : BF::$path_to_framework.BF::gc('path_to_root');
		
		/*
		 * Set propoer PHP_SELF
		 */
		$self = $_SERVER['PHP_SELF'];
		if (strpos($_SERVER['PHP_SELF'], ':') !== false) $self = trim(substr($self, strrpos($self, ':')));
		
		/**
		 * Detect base URL if not manually set in config
		 * Detection need urlrewrite to be on and .htaccess file to be processed
		 */
		BF::$path_info = isset($_SERVER['ORIG_PATH_INFO'])? $_SERVER['ORIG_PATH_INFO']: (isset($_SERVER['PATH_INFO'])?$_SERVER['PATH_INFO']:'');
		if (!BF::gc("auto_detect_base_url")) {
			BF::$base_url = BF::gc("base_url");
		} else {
			$self = $_SERVER['PHP_SELF'];
			if (BF::$path_info) {
				if (substr($self, -strlen(BF::$path_info)) ==  BF::$path_info) {
					$self = substr($self, 0, -strlen(BF::$path_info));
				} else {
					die ("<h3>Failed detecting base url</h3>Please make sure your webserver gives proper values for PATH_INFO and PHP_SELF. You can also set this manually in the <i>Admin Console</i>");
				}
			}
			BF::$base_url = substr($self, 0, strrpos($self, '/')+1);
		}

		/**
		 * Detect /-separated parameters
		 */
		if (strlen(BF::$path_info) >= 1 && BF::$path_info[0] == '/') {
			BF::$path_info_array = explode('/', substr(BF::$path_info, 1));
		}
		
// to remove
/*
		if (!BF::gc("auto_detect_base_url")) BF::$base_url = BF::gc("base_url");
		elseif ( isset($_SERVER['PHP_SELF']) && isset($_SERVER['RELATIVE_FROM_ROOT'])) {
			$relative = $_SERVER['RELATIVE_FROM_ROOT'];
			if ($relative == '') {
				BF::$base_url = substr($self, 0, strrpos($self, '/')+1);
			} else {
				if (substr($self, -strlen($relative)) ==  $relative ) {
					BF::$base_url = substr($self, 0, -strlen($relative));
				} else {
					// try adding the index.php if ends with a /
					if ($relative[strlen($relative)-1] == '/') $relative .= 'index.php'; 
					if (substr($self, -strlen($relative)) ==  $relative ) {
						BF::$base_url = substr($self, 0, -strlen($relative));
					} else {
						die ("<h3>Failed detecting base url</h3>Please contact webmaster.<br><br>Webmaster, urlrewrite is on and .htaccess gets parsed, but the data is incorrect. Depending on server configuration you may need to set this manually in the <i>Admin Console</i>");
					}
				}
			}
		
			// if using URL locale, do one more test
			if (BF::gc('locale_enabled') && BF::gc('locale_use_url')) {
				$base = BF::$base_url.(isset($_GET['detected_locale'])? $_GET['detected_locale'].'/':'');
				if (substr($_SERVER['REQUEST_URI'], 0, strlen($base)) != $base) die ("<h3>Improper base url detected/given in config</h3>Please contact webmaster.<br><br>Webmaster, you are using URL to transmit locale, make sure urlrewrite is on and the right .htaccess file gets parsed. Beginning of REQUEST_URI mismatch base url.");
			}
		
		} else {
			die ("<h3>Failed detecting base url</h3>Please contact webmaster.<br><br>Webmaster, make sure urlrewrite is on and the right .htaccess file gets parsed. Depending on server configuration you may need to set this manually in the <i>Admin Console</i>");
		}
		*/


		/**
		 * TIMEZONE
		 */
		if (function_exists('date_default_timezone_set')) date_default_timezone_set(BF::gc('time_default_zone'));
		
		/**
		 * ERRORS HANDLING
		 */
		// Set error reporting level
		error_reporting(BF::gc('error_reporting'));
		// set default exception handler
		set_exception_handler(array('BF', 'exception_handler'));
		// set the user defined error handler
		set_error_handler(array('BF', 'error_handler'));
		
		/**
		 * Encoding
		 */
		BF::$encoding = BF::gc('encoding');
		
		/**
		 * Session
		 */
		if (session_id() == '') session_start();
		
		/**
		 * Quoting function
		 */
		BF::load_module('q');
		
//		// if we need to keep track of activity time
//		//if (BF::gc('user_inactivity_time') != 0 && isset($_SESSION['BF_'.BF::gc('project_id').'_activity_time']) && $_SESSION['BF_'.BF::gc('project_id').'_activity_time'] > BF::$time - 3600*BF::gc('user_inactivity_time')) $_SESSION['BF_'.BF::gc('project_id').'_activity_time'] = BF::$time;
		
		/**
		 * Clear all slashes added by magic_quote
		 */
		if (get_magic_quotes_gpc()) {
			function BF_stripslashes_deep($value) {
				return is_array($value) ? array_map('BF_stripslashes_deep', $value) : stripslashes($value);
			}
			$_POST = array_map('BF_stripslashes_deep', $_POST);
			$_GET = array_map('BF_stripslashes_deep', $_GET);
			$_COOKIE = array_map('BF_stripslashes_deep', $_COOKIE);
			$_REQUEST = array_map('BF_stripslashes_deep', $_REQUEST);
		}
		
		/**
		 * Load project specific file
		 */
		if (BF::gc('general_file_to_load')) BF::gr(BF::gc('general_file_to_load'))->load();
		
		
		/**
		 * ACCESS CHECK
		 */
		if ($access !== null && !BF::ga($access)) throw new BF_forbidden();
	}
	
	/**
	 * Framework error handler
	 */
	public static function error_handler($code, $string, $file, $line, $context) {
	    // if error has been supressed with an @, should not be considered
	    if (!($code & error_reporting())) return true;
		BF::load_module('BF_php_exception');
		// throw exception
		throw new BF_php_exception($code, $string, $file, $line, $context);
	}
	/**
	 * Framework exception handler
	 * @param	exception	$e : exception object
	 */
	public static function exception_handler($e) {
		try {
			try {
				BF::load_module("BF_error");
			} catch (exception $e) {
				die("FATAL ERROR: Failed loading error repporting module BF_error");
			}
			$error = new BF_error($e);
			$error->display(BF::$error_output_callback);
		} catch (exception $new_e) {
			print('Fatal Error. Second exception occured while handling exception.');
		}
	}

	/**
	 * Register error display handler
	 */
	public static function register_error_output_callback($callback) {
		BF::$error_output_callback = $callback;
	}
	
	/**
	 * Check whether using secure connection (https)
	 */
	public static function is_secure() {
		return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off';
	}

	
	/**
	 * Function to get a value from the config array.
	 * @param	string	$key, [$key, ...] : Config entry key and subkeys
	 * @return	mixed
	 */
	public static function gc() {
		$keys = func_get_args();
		$value =& BF::$conf;
		try {
			for ($i = 0, $count = count($keys); $i < $count; $i++) {
				if (!array_key_exists($keys[$i], $value)) throw new exception();
				$value =& $value[$keys[$i]];
			}
		} catch (exception $e) {
			throw new exception('Trying to access undefined config entry : Config -> '.implode(' -> ', array_slice($keys, 0, $i+1)));
		}
		return $value;
	}
	
	/**
	 * Load a module from the modules folder. The name of the module correspond to the name of the php file, without '.php'
	 * @param	string	$module_name : name of the module to load
	 * @warning	Will trigger a fatal error if file cannot be found
	 * return	bool	true
	 */ 
	public static function load_module ($module_name) {
		// try to load module (will trigger fatal error if failed)
		try {
			return BF::gr($module_name.".php", 'modules')->load_once();
		} catch (exception $e) {
			throw new exception("Failed loading module : ".$module_name." (".$e->getMessage().")");
		}
	}
	
	/**
	 * Load a model from the models folder. The name of the model correspond to the name of the php file, without '.php'
	 * @param	string	$model_name : name of the model to load
	 * @warning	Will trigger a fatal error if file cannot be found
	 * return	bool	true
	 */ 
	public static function load_model ($model_name) {
		// try to load module (will trigger fatal error if failed)
		try {
			return BF::gr($model_name.".php", 'model')->load_once();
		} catch (exception $e) {
			throw new exception("Failed loading model : ".$model_name." (".$e->getMessage().")");
		}
	}
	
	/**
	 * function to get the folder object from a folder ID
	 * @param 	string	$id_folder [optional default null] : ID of the folder (as defined in the config file), null for root directory
	 * @return 	folder object
	 */
	public static function gf($id_folder = null) {
		if (!isset(BF::$folders[$id_folder? $id_folder:'@INDEX@'])) BF::$folders[$id_folder? $id_folder:'@INDEX@'] = new BF_folder($id_folder);
		return BF::$folders[$id_folder? $id_folder:'@INDEX@'];
	}

	/**
	 * function to get a ressource object from a RID string
	 * @param 	string	$rid : RID of ressource, see doc for syntax
	 * @param	string	$default_folder_id [optional default null] : Default folder if not set in RID. Null for root
	 * @return 	ressource object
	 */
	public static function gr($rid, $default_folder_id = null) {
		return new BF_ressource($rid, $default_folder_id);
	}
	
	
	/**
	 * DATABASE CONNECTION
	 */
	
	/**
	 * Connect to a DB with a DB ID as defined in the configuration file. 
	 * @param	string	$id_db [optional default null] : DB ID as defined in the config file. Null for default connection (first one defined in config)
	 * @return	db object
	 */
	public static function gdb($id_db = NULL) {
		/// Contains all opened connections
		static $connections = array();
		static $default = null;
		if (!$id_db && $default === null) $default = current(array_keys(BF::gc('db')));
		if (!$id_db) $id_db = $default;
		
		if (!isset($connections[$id_db])) {
			// get DB params
			$db = BF::gc('db', $id_db);
			$class = 'BF_DB_'.$db[0];
			BF::load_module($class);
			$connections[$id_db] = new $class($db[1], $db[2], $db[3], $db[4]);
		}
		return $connections[$id_db];
	}
	
	
	/**
	* List objects that match condition
	* @param	string	$class_name : model class name of objects to be listed
	* @param	string	$condition [optional default NULL] : SQL query condition
	* @param	string	$extra [optional default NULL] : append some commands at the end of the SQL query (such as ORDER BY, LIMIT)
	* @return	array[user object]
	*/
	public static function glist($class_name, $condition = NULL, $extra = NULL) {
		BF::load_module("BF_DB_list");
		BF::load_model($class_name);
		return new BF_DB_list($class_name, $condition, $extra);
	}
	
	/**
	* Count objects that match condition
	* @param	string	$class_name : model class name of objects to be counted
	* @param	string	$condition [optional default NULL] : SQL query condition
	* @param	string	$extra [optional default NULL] : append some commands at the end of the SQL query (such as LIMIT)
	* @return	int
	*/
	public static function gcount($class_name, $condition = NULL, $extra = NULL) {
		BF::load_module("BF_DB_list");
		BF::load_model($class_name);
		return BF::gdb($class_name::$db)->count($class_name::$table, $condition, $extra);
	}
	
	/**
	 * Function to return current user (object)
	 * It wil be used for identification, access check
	 * @param	object	$set [optional default null] : Overrid the current logged user with either another user or false (when current user logout)
	 * @return	Object of current user or false if no user currently logged
	 */
	public static function gu ($set = null) {
		static $user = null;
		if ($set !== null) return $user = $set;
		if ($user === null) {
			BF::load_model('user');
			$user = user::get_logged_user();
		}
		return $user;
	}
	
	/**
	 * Check if current logged user match a certain access string
	 * @param	string	$access_string : Access string. See manual for syntax. Eg. uid:4|-g:groupA&g:groupB
	 * @param	user	$user [optional default null] : User to test, null for current user
	 * @return bool	true if match, false otherwise
	 */
	public static function ga ($access_string, $user = null) {
		if ($user === null) $user = BF::gu();
		// split with |
		foreach (explode('|', strtolower((string)$access_string)) as $sub_access_string) {
			$sub_match = true;
			// split with &
			foreach (explode('&', $sub_access_string) as $condition) {
				$condition = trim($condition);
				if ( (!$user && $condition != '-1' && $condition != '') || ($user && !$user->ga_condition($condition)) ) {
					$sub_match = false;
					break;
				}
			}
			if ($sub_match) return true;
		}
		return false;
	}
	
	/**
	 * Give some info on browser
	 * @param	string	$property [optional default ''] : Name of the property (case in-sensitive) to retrieve. '' for browser array instead as returend by browscap.
	 * @return	mixed	Value of property or browser array that was returned by Browscap
	 */
	public static function gb($property = '') {
		$property = strtolower($property);
		static $cached_browscap = null;	// will either be cached array OR the object itself if it was loaded
		static $cached_js_detect = null;
		switch ($property) {
			// Properties that are not returned by Browscap
			//case 'Accept' : return explode(",", strtolower( $_SERVER["HTTP_ACCEPT_LANGUAGE"])); break;	// useless since IE always return */*
			case 'languages' : 
				return isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])? explode(",", strtolower( $_SERVER["HTTP_ACCEPT_LANGUAGE"])):array();
			case 'country' : 
				throw new exception('TODO : Not available yet');
			case 'javascript_detected' : 
				return isset($_SESSION['BF_'.BF::gc('project_id').'_cached_js_detect']);
			case 'js_browser' :
			case 'js_browser_version' :
			case 'js_flash' :
			case 'js_resolution' :
				if (!isset($_SESSION['BF_'.BF::gc('project_id').'_cached_js_detect'])) return null;
				if (!isset($cached_js_detect)) $cached_js_detect = array_combine(Array('js_browser','js_browser_version','js_flash','js_resolution'), explode('|', $_SESSION['BF_'.BF::gc('project_id').'_cached_js_detect']));
				return $cached_js_detect[$property];
			// Properties that are returned by Browscap
			// cached properties
			case 'browser' : 
			case 'version' : 
			case 'platform' :
			case 'win32' :
			case 'majorver' : 
			case 'wap' :
			case 'ismobiledevice' :
			case 'crawler' :
				if ($cached_browscap) return is_array($cached_browscap)? $cached_browscap[$property] : $cached_browscap->$property;
				elseif (isset($_SESSION['BF_'.BF::gc('project_id').'cached_browscap'])) {
					$cached_browscap = array_combine(Array('browser','version','platform','win32','majorver','wap','ismobiledevice','crawler'), explode('|', $_SESSION['BF_'.BF::gc('project_id').'_cached_browscap']));
					return $cached_browscap[$property];
				}
			// not cached properties
			default :  
				if (!$cached_browscap || !is_object($cached_browscap)) {
					if (!BF::gf('browscap')) throw new exception('Invalid config entry for browscap folder path');
					BF::load_module('Browscap');
					$previous_error_reporting = error_reporting();
					error_reporting($previous_error_reporting ^ E_STRICT ^ E_NOTICE);	// shut off notices
					$bc = new Browscap(gf('browscap')->path);
					$cached_browscap = array_change_key_case($bc->getBrowser(null, true),CASE_LOWER);
					error_reporting($previous_error_reporting);
					if (!isset($_SESSION['BF_'.BF::gc('project_id').'_cached_browscap'])) {
						$_SESSION['BF_'.BF::gc('project_id').'_cached_browscap'] = implode('|', Array($cached_browscap['browser'],$cached_browscap['version'],$cached_browscap['platform'],$cached_browscap['win32'],$cached_browscap['majorver'],$cached_browscap['wap'],$cached_browscap['ismobiledevice'],$cached_browscap['crawler']));
					} 
				}
				if ($property) return $cached_browscap[$property];
				else return $cached_browscap;
		}
	}
	
	/**
	 * Load current locale object
	 * @warning	lang code should always be in lower case, while country code in uper case
	 * @return	locale object
	 */
	public static function gl($locale_string = '') {
		static $locale = null;
		if ($locale == null) {
			if (BF::gc('locale_enabled')) {
				BF::load_module('locale');
				$locale = new BF_locale();
			} else $locale = new BF_locale_disabled();
		}
		return $locale;
	}

	/**
	 * Get value from query string (either using GET or /-separated parameters)
	 * @param	mixed	$key : Either string key of get param or int position of /-separated parameter
	 * @param	mixed	$key2 : Second option if first key is not set
	 * @return	mixed	Value at key or null if not set
	 */
	public static function gg($key, $key2 = null) {
		if (is_int($key) && $key < 0) $key = count(BF::$path_info_array) + $key;
		if (is_int($key) && isset(BF::$path_info_array[$key])) return BF::$path_info_array[$key];
		elseif (is_string($key) && isset($_GET[$key])) return $_GET[$key];
		else return $key2 === null? null : BF::gg($key2);
	}
}

/**
 * Exception for user displaying a user message
 * Should be used when non critical, and has been caused by the user
 * Shows the error message window to the user. Output will depend of what http client is asking, can be html, xml, or ajax
 */
abstract class BF_user_exception extends exception {
	/**
	 * Constructor
	 * @param	string	$message [optional default null] : A message to display to user (will be translated if needed). Null for default message
	 * @param	string	$title [optional default null'] : Title of the error box to display (will be translated if needed). Null for default title
	 */
	public function __construct($message = null, $title = null) {
		// set the display param
		$this->display = Array('message' => $message, 'title' => $title);
		parent::__construct($message);
	}
}
class BF_internal extends BF_user_exception {
	public function __constructor($message, $title) {
		parent::__construct($message, $title);
	}
}
class BF_forbidden extends BF_user_exception {
	public function __constructor($message, $title) {
		parent::__construct($message, $title);
	}
}
class BF_information extends BF_user_exception {
	public function __constructor($message, $title) {
		parent::__construct($message, $title);
	}
}
class BF_not_found extends BF_user_exception {
	public function __constructor($message, $title) {
		parent::__construct($message, $title);
	}
}
class BF_invalid_form extends BF_user_exception {
	public function __constructor($message, $title) {
		parent::__construct($message, $title);
	}
}
/**
 * Ressources
 *
 * Class for accessing ressources based on a RID (Ressource ID, usually composed of a folder ID and a sub path)
 * eg. "/img/sub/test.jpg/foo/bar?a=b#anchor" is read as folder ID = img and sub path = ./sub/test.jpg/foo/bar?a=b#anchor
 * Supports RIDs and URLs with a protocol scheme 
 * Applies parse_magic_keyword to path
 */
class BF_ressource {
	const RID = 1, URL = 2;
	private $type;
	private $folder;
	private $sub;
	
	/**
	 * Constructor : parse a RID
	 */
	public function __construct($rid, $default_folder_id = "") {

		if (strpos($rid, "://")) {
			$this->type = self::URL;
			$this->sub = $rid;
		} else {
			$this->type = self::RID;
			if (strpos('/'.$rid.'/', '/../') !== false) throw new exception("For security concern, Ressource ID should not contain link to parent folder (../). RID : '".$path."' is not valid");
			if ($rid[0] == "/") {
				if (strpos($rid, "/", 1) === false) $rid .= '/';
				$folderID = substr($rid, 1, strpos($rid, "/", 1)-1);
				$this->sub = substr($rid, strpos($rid, "/", 1)+1);
			} else {
				$folderID = $default_folder_id;
				$this->sub = $rid;
			}
			$this->folder = BF::gf($folderID);
		}
	}
	/**
	 * Return server path to ressource
	 * @return	An absolute path to file
	 */
	public function path() {
		if ($this->type == self::URL) return $this->sub;
		return $this->folder->path().$this->sub;
	}
	/**
	 * Generate a url to ressource
	 * @param	String	$locale [optional default null] : Set a different locale for page (if site is locale-enabled and uses url to transmit locale)
	 * @return	An URL to ressource 
	 */
	public function url ($locale = null) {
		if ($this->type == self::URL) return $this->sub;
		return $this->folder->url($locale).$this->sub;
	}
	
	/**
	 * Retreive associated folder object
	 * @return	A folder object for valid RID, null otherwise
	 */
	public function folder () {
		return $this->type == self::URL? null : $this->folder;
	}
	
	/**
	 * Get ressource type
	 * @return	BF_ressource::URL or BF_ressource::RID
	 */
	public function type() {
		return $this->type;
	}
	
	/**
	 * Redirect to ressource
	 * @param	String	$locale [optional default null] : Set a different locale for page (if site is locale-enabled and uses url to transmit locale)
	 * @param	bool	$secure [optional default null] : if true force https, if false force http, if null keep current potocole
	 * @warning No output must be made prior to redirection for headers to be valid.
	 */
	public function redirect($locale = null, $secure = null) {
		header("Location: http".($secure === true || ($secure === null && BF::is_secure())? 's':'')."://" . $_SERVER['HTTP_HOST'].$this->url($locale));
		exit();
	}
	/**
	 * Load ressource as a PHP script (performs a require on the file)
	 * Throws exception if file does not exists, and fatal error on failure
	 */
	public function load() {
		if (BF::gc("error_debug")) {
			$include = include ($this->path());
		} else {
			$include = @include ($this->path());
		}
		if ($include === false) throw new exception('Failed loading file : '.$this->sub.' (full path: '.$this->path());
		return $include;
	}
	/**
	 * Load ressource once as a PHP script (performs a require once on the file)
	 * Throws exception if file does not exists, and fatal error on failure
	 */
	public function load_once() { 
		if (BF::gc("error_debug")) {
			$include = include_once ($this->path());
		} else {
			$include = @include_once ($this->path());
		}
		if ($include === false) throw new exception('Failed loading file : '.$this->sub.' (full path: '.$this->path());
		return $include;
	}
	
	/**
	 * Determine if ressource exists
	 * @return	bool
	 */
	public function exists() {
		return file_exists($this->path());
	}
}

	
/**
 * FOLDERS
 */

/**
 * Class for accessing folders defined in config
 * ID of the folder, as set in the config file need to be given.
 * This class enable folders name's to be changed without the need to modifiy other scripts than the config file.
 */
class BF_folder {
	const RELATIVE = 1, ABSOLUTE = 2, REMOTE = 3;
	private $type;
	/**
	 * @var	string	ID of the folder
	 */
	private $id = NULL;
	private $path;
	/**
	 * Constructor
	 * @param	string	$id_folder [optional default null] : ID of the folder as specified in the config file, null for the root folder
	 */
	public function __construct($id_folder = null) {
		$this->id = $id_folder;
		if ($id_folder == null) {
			$this->type = self::RELATIVE;
			$this->path = '';
		} else {
			try {
				$this->path = BF::gc("folder_".$id_folder);
			} catch (exception $e) {
				throw new exception("Folder '".$id_folder."' is not defined in config (".$e->getMessage().")");
			}
			// Absolute paths start with a '/' and remote URL should contain a ':'
			if ($this->path[0] == '/') $this->type = self::ABSOLUTE;
			elseif (strpos($this->path, ':') !== false) $this->type = self::REMOTE;
			else $this->type = self::RELATIVE;
		}
	}
	/**
	 * Determine if folder or a sub folder/file exists
	 * @return	bool
	 */
	public function exists() {
		return is_dir($this->path());
	}
	/**
	 * List folder's content
	 * @param	string	$sub [optional] : Specify a subdirectory to list instead
	 * @param	int 	$type [optional default 0] : Restrict the type of result : 1 for files only, 2 for folder only, 0 for all
	 * @param	callback	$filter_callback [optional default false] : Apply a filter on result before return
	 * @return	array	Array containing the names of the files/folders
	 */
	public function scan($sub = "", $type = 0, $filter_callback = false) {
		$return = Array();
		$path = $this->path().$sub.($sub == '' || $sub[strlen($sub)-1] == '/'? '':'/');
		$dir = dir($path);
		while (false !== ($entry = $dir->read())) {
			if ($entry == '.' || $entry == '..') continue;
			if ($type != 0 && is_dir($path.$entry) == ($type == 1)) continue;
			$return[] = $entry;
		}
		$dir->close();
		return $filter_callback? array_filter($return, $filter_callback) : $return;
	}
	/**
	 * Get folder's ID
	 */
	public function get_id() {
		return $this->id;
	}
	/**
	 * Return server path to folder
	 * @return	An absolute path to folder
	 */
	public function path() {
		switch ($this->type) {
			case self::REMOTE : 
			case self::ABSOLUTE : return $this->path;
			default : return BF::$path_to_root.$this->path;
		}
	}
	/**
	 * Generate a url to folder
	 * @param	String	$locale [optional default null] : Set a different locale for url (if site is locale-enabled and uses url to transmit locale)
	 * @return	An URL to ressource 
	 */
	public function url ($locale = null) {
		switch ($this->type) {
			case self::REMOTE : return $this->path;
			case self::ABSOLUTE : throw new exception("Folder '".$this->id."' has an absolute path and cannot be accessed from outside, it has no URL");
			default : 
				if (BF::gc('locale_enabled')) return BF::gl()->format_url(BF::$base_url, $this->path, $locale);
				else return BF::$base_url.$this->path;
		}
	}
}
	
		
/**
 * Define a locale class that is used if not using locale
 */
class BF_locale_disabled {
	public $lang = 'en', $country = 'US', $locale = 'en_US';
	public function tl($text) {return $text;}
	public function tl_tag($text, $tag = '') {return '['.$text.']';}
	public function block_exists($block) {return true;}
}


// End time counter for framework loading
//$_framework_time_end = (float) array_sum(explode(' ', microtime()));

?>
