<?php

/*-------------------------------------------------------*\
|  PHP BasicFramework - Ready to use PHP site framework   |
|  License: LGPL, see LICENSE                             |
\*-------------------------------------------------------*/

/**
* Output plain text module
* @file output.plain.php
* @package PhpBF
* @subpackage plain_output
* @version 0.7
* @author Loic Minghetti
* @date Started on the 2008-07-10
*/

// Security
if (!defined('C_SECURITY')) exit;

BF::load_module("BF_output");

/**
 * Class interface for all output modules
 */
class BF_output_plain {
	function __construct() {
		BF::register_error_output_callback (array($this, "show_error"));
	}
	
	/**
	 * @var 	string	$default_content_type : Content_type to use by defult
	 */
	public $content_type = 'text/plain';
	/**
	 * Show an error using this output module
	 * @param	string	$type : Appearance of error box (EXCEPTION_INTERNAL, EXCEPTION_INFORMATION, EXCEPTION_ACCESS, EXCEPTION_NOT_FOUND, EXCEPTION_INVALID_FORM).
	 * @param	string	$message : A message to display to user
	 * @param	string	$title : Title of the error box to display
	 * @param	mixed	$debug_message : Debug message to display, or false if not in debug mode
	 */
	public function show_error($type, $message, $title) {
		$this->send ($title."\n\n".$message);
		die();
	}
	/**
	 * Send content to output
	 * @param	string	$content : Content to send
	 * @return	bool
	 */
	public function send($content) {
		BF_output::send_content_type($this->content_type);
		print ($content);
	}
	/**
	 * Terminate script. May optionaly take a last string to output (depending of module). Equivalent to a die
	 * @param	string	$string [optional default null] : Content to ouput before closing (may be ingored by some output modules)
	 */
	public function close($string = null) {
		$this->send($string);
		die();
	}
}


?>
