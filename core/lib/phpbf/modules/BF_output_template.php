<?php

/*-------------------------------------------------------*\
|  PHP BasicFramework - Ready to use PHP site framework   |
|  License: LGPL, see LICENSE                             |
\*-------------------------------------------------------*/

/**
* Template output module
* @file output.template.php
* @package PhpBF
* @subpackage template_output
* @version 0.7
* @author Loics Minghetti
* @date Started on the 2007-04-20
*/

// Security
if (!defined('C_SECURITY')) exit;

BF::load_module("BF_output");

// Define location of the Smarty template engine required for the template module
define("SMARTY_DIR", BF::gf('smarty')->path());
require_once(SMARTY_DIR."Smarty.class.php");


/**
 * template class, used for displaying smarty templates
 */
class BF_output_template extends Smarty  {

	/**
	 * @var	array	$this->data : contains all informations about the template and will be available in the template (as $_data)
	 */
	public $data;
	/**
	 * @var	array	$this->c : contains all color schemes (associatvie array) and selected color scheme (indexed array)
	 */
	public $c;
	/**
	 * @var 	string	$content_type : Default content type
	 */
	public $content_type = 'text/html';
	/**
	 * @var	static array	$this->global_functions : List of functions that may be called from a smarty template
	 */
	public static $global_functions = array('gc','gu','ga','gl','q','gg');//array('g[a-z]{1,2}','q'); // array('gc', 'gu', 'gf', 'gdb', 'gt', 'ga', 'gl','q');
	/**
	 * Constructor
	 * @param 	string	$template [optional] : name of the template to load (without the .tpl)
	 * @param 	array	$info [optional default Array()] : array containing page's meta informations, title, cache, cache_version, etc...
	 * 					cach_lifetime : false or 0 to disactivate caching, null for default, or int to set cache lifetime
	 * 					Set cache_version if you want to separate cache into versions
	 */
	public function __construct($template = false, $info = array()) {

		BF::register_error_output_callback (array($this, "show_error"));
		
	// > Define runtime variables
		parent::__construct();
		$this->compile_check = BF::gc('template_compile_check');
		$this->debugging = BF::gc('template_smarty_debug');
		$this->template_dir = BF::gf('tpl')->path(); 
		$this->compile_dir = BF::gf('compiled')->path();
		$this->cache_dir = BF::gf('cached')->path();
		$this->addPluginsDir(BF::gf('tags')->path());
		$this->content_type = BF::gc('template_default_content_type');
		$this->error_reporting = BF::gc('error_reporting');
		
	// > Caching
		// block for non-cachable content
		$this->cache_lifetime = BF::gc('template_cache_lifetime');
		$this->caching = BF::gc('template_cache')? 1:0;
		//$this->cache_version = null;
		
	// > Register template infos
	
		$this->data = array();
		$this->assignByRef("_data", $this->data);
		
	// > Register common template functions and blocks. Others will be found in the tags dir
		
		$this->loadFilter('pre', 'translate');
		
		// trim top and bottom
		$this->registerFilter("output", create_function('$x', 'return trim($x);'));
		
		
		if ($template !== false) $this->load($template, $info);
	}

	
	/**
	 * Load a template
	 * @param 	string	$template : name of the template to load (without the .tpl)
	 * @param 	array	$info [optional default Array()] : array containing page's meta informations, title, cache, cache_version, etc...
	 * 					cach_lifetime : false or 0 to disactivate caching, null for default, or int to set cache lifetime
	 * 					Set cache_version if you want to separate cache into versions
	 * @return	object	Return reference to self (so that you can chain it like : BF::output('template')->load('tpl')->display();
	 */
	public function load($template, $info = array()) {
		
		if (!is_array($info)) $info = array();
		
		// assign page's informations
		$this->data['template'] = $template;
		$this->data['window_title'] = BF::gc("window_title");
		$this->data['title'] = isset($info['title'])? $info['title'] : '';
	 	$this->data['keywords'] = isset($info['keywords'])? $info['keywords'] : '';
	 	$this->data['description'] = isset($info['description'])? $info['description'] : '';
	 	$this->data['creator'] = isset($info['creator'])? $info['creator'] : BF::gc('template_default_creator');
	 	$this->data['subject'] = isset($info['subject'])? $info['subject'] : '';
	 	$this->data['encoding'] = BF::$encoding;
	 	$this->data['content_type'] = $this->content_type;
		$this->data['debug_mode'] = BF::gc('error_debug');	// this may be useful to tell JS whever to debug or not
		$this->data['cache_version'] = isset($info['cache_version'])? $info['cache_version'] : '';	// Set cache version name
		
		//if (isset($info['cache_version'])) $cache_version .= (strlen($cache_version)? '|':'').$info['cache_version'];
		/*
// TODO		// Caching
		if (array_key_exists('cache', $info)) {
			if ($info['cache'] === true) $this->caching = true;
			elseif ($info['cache'] === false || (int)$info['cache'] === 0) $this->caching = false;
			else {
				$this->cache_lifetime = (int)$info['cache'];
				$this->caching = 1;
			}
		}*/
		
		return $this;
	}
	
	/**
	 * Returns whever current loaded template is cached or not
	 * @return	bool	true if cached
	 */
	public function cached() {
		return parent::is_cached($this->data['template'].".tpl", $this->data['cache_version'], BF::gc('locale_enabled')? BF::gl()->locale : null);
	}
	
	/**
	 * Display/fetch template template
	 * @param	bool	$display [optional default true] : Set to false to return content instead of displaying it
	 * @return	mixed	Template ouput, unless $display is true (returns void)
	 */
	public function disp($display = true) {
		// Display template
		if (!isset($this->data['template']) || !$this->data['template']) throw new exception('No template loaded');
		if ($display) {
			BF_output::send_content_type($this->content_type);
			return $this->display($this->data['template'].".tpl", $this->data['cache_version'], BF::gc('locale_enabled')? BF::gl()->locale : null);
		} else {
			return $this->fetch($this->data['template'].".tpl", $this->data['cache_version'], BF::gc('locale_enabled')? BF::gl()->locale : null);
		}
	}

	/**
	 * Show error message
	 */
	public function show_error($type, $message, $title, $debug = null) {
		$this->assign('message', $message);
		$this->assign('title', $title);
		$this->assign('type', $type);
		$this->assign('debug', $debug);
		
		$this->load(BF::gc('page_error'));
		$this->disp();
		die();
	}
	
	
	/**
	 * Function that prints out an attribute for an html tag.
	 * @param	string	$name : Name of the html attribute
	 * @param	string	$value [optional default null] : Value of the html attribute. Value will be single quoted and escaped
	 * @param	string	$default [optional default null] : Default value for the html attribute if value is empty (if null, then attribute will be obmited)
	 */
	public function attr($name, $value = NULL, $default = NULL) {
		return $value !== NULL || $default !== NULL? " ".strtolower($name)."=".Q($value !== NULL? $value : $default, Q_HTML) : "";
	}
	
	/**
	 * Generate an url
	 * @param	string	$href [optional default false] : file to point, see doc of BF::gr function for detail 
	 * @param	string	$locale [optional default false] : force a locale for href (or current page if href is false)
	 * @return	string	url
	*/
	public function make_url ($href = false, $locale = null) {
		if ($href === false && $locale != null && BF::gc('locale_enabled')) {
			if (BF::gc('locale_use_url')) {
				$base = BF::$base_url.(isset($_GET['detected_locale'])? $_GET['detected_locale'].'/':'');
				$url = substr($_SERVER['REQUEST_URI'], strlen($base));
				return BF::gr($url)->url($locale);
			} else {
				return BF::gl()->format_url('', $_SERVER['REQUEST_URI'], $p['locale']);
			}
		} else {
			return BF::gr($href)->url($locale);
		}
	}
}

?>
