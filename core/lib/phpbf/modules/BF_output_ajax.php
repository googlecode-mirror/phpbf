<?php

/*-------------------------------------------------------*\
|  PHP BasicFramework - Ready to use PHP site framework   |
|  License: LGPL, see LICENSE                             |
\*-------------------------------------------------------*/

/**
* Output Ajax module
* @file output.ajax.php
* @package PhpBF
* @subpackage ajax
* @version 0.7
* @author L. Minghetti
* @date Started on the 2007-12-29
*/

// Security
if (!defined('C_SECURITY')) exit;

BF::load_module("BF_output");

class BF_output_ajax {
	
	public $content_type = null;
	
	
	public function send_js($content = '') {
		if (!$this->content_type) $this->content_type = 'text/javascript';
		return $this->send($content);
	}
	public function send_json($var = '') {
		if (!$this->content_type) $this->content_type = 'application/json';
		return $this->close(Q($var,Q_JSON));
	}
	public function send_html($html = '') {
		if (!$this->content_type) $this->content_type = 'text/html';
		return print $html;
	}
	
	
	public function send($message = '') {
		if (!$this->content_type) $this->content_type = 'text/plain';
		BF::send_content_type($this->content_type);
		return print $message;
	}
	
	/**
	 * Send a string to browser and stop script
	 * @param	string	$return [optional default ''] : String to send
	 * @return	void
	 */
	public function close($string = '') {
		$this->send($string);
		exit();
	}
	
	/**
	 * Show error
	 */
	public function show_error($type, $message, $title, $debug_message = false) {
		$this->send_js("alert(\n".str_replace("\n", '\n', Q($title."\n\n".$message.($debug_message? "\n\nDebug: ".$debug_message:''), Q_STRING))."\n);");
	}
	
}

?>
