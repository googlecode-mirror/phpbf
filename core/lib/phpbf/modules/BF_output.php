<?php

/*-------------------------------------------------------*\
|  PHP BasicFramework - Ready to use PHP site framework   |
|  License: LGPL, see LICENSE                             |
\*-------------------------------------------------------*/

/**
* Output generic module
* @file BF_output.php
* @package PhpBF
* @subpackage plain_output
* @version 0.7
* @author Loic Minghetti
* @date Started on the 2012-05-02
*/

// Security
if (!defined('C_SECURITY')) exit;

/**
 * Class for all output modules common functions
 */
class BF_output {

	/**
	 * Send content type header. Only first call to this mehod will send header, following calls will have no effect
	 * @param	string	$content_type : Content type (eg. 'text/html')
	 * @return	bool	true on success, false if headers have already been sent
	 */
	public static function send_content_type($content_type) {
		static $headers_sent = false;
		if ($headers_sent) return false;
		// check
		if (trim($content_type) == '') {
			throw new exception('Content type cannot be empty');
		}
		if (headers_sent()) {
			$file = $line = null;
			headers_sent($file, $line);
			throw new exception('Headers have already been sent at '.$file.', line '.$line);
		}
		// sned
		
		$headers_sent = true;
		return header('Content-type: '.trim($content_type).(BF::$encoding? '; charset='.strtolower(BF::$encoding):''));
	}
}


?>
