<?php

/*-------------------------------------------------------*\
|  PHP BasicFramework - Ready to use PHP site framework   |
|  License: LGPL, see LICENSE                             |
\*-------------------------------------------------------*/

/**
* Q - Quoting module
* @file q.php
* @package PHPBasicFramework
* @version 0.7
* @author L. Minghetti
* @date Started on the 2009-06-25
*/

// Security
if (!defined('C_SECURITY')) exit;

/**
 * QUOTING FOR HTML AND SQL QUERIES
 */
 
/**
 * Function to quote a variable into a string. Depending on flags, quotes (double by default) will be added
 * @param	int/string 	$flags [optional default Q_ALL] :
 * IF flag is a string : SQL quoting for given class name (based on what is required from database used)
 * IF flag is int : 
 * For value === NULL or value === ''
 * 		- if Q_NULL => 'null'
 * 		- if Q_STRING => '""'
 * 		- if Q_INT or Q_FLOAT => '0'
 * 		- if Q_BOOL => 'false'
 * For value === TRUE / FALSE
 * 		- if Q_BOOL => 'true' / 'false'
 * 		- if Q_INT or Q_FLOAT => '1' / '0'
 * 		- if Q_STRING => '"1"' / '""'
 * For value is an INT (123)
 * 		- if Q_INT => '123'
 * 		- if Q_STRING => '"123"'
 * 		- if Q_BOOL => 'true' (if int!=0) / 'false' (if int==0)
 * For value is a FLOAT (12.3)
 * 		- if Q_FLOAT => '12.3'
 * 		else round and act like an INT
 * For value is a string not empty ('abc')
 * 		- if Q_STRING => '"abc"'
 * 		- if Q_INT or Q_FLOAT => cast to numeric and act as an int/float
 * 		- if Q_BOOL => 'true'
 * For value is array or object : 
 * 		- if Q_ARRAY => print_r array/object and act as string
 * 
 * Q_FLOAT implies Q_INT
 * Q_ESCAPE_HTML : Escape quotes and all other html entities into their HTML code (see htmlspecialchars on php doc)
 * Q_JSON_WRAPER : Wrap variables in JSON structure (ie. {} or [] if array, normal quotes otherwise)
 * Q_JSON : Format in JSON style. Implies Q_ALL, Q_ARRAY and Q_JSON_WRAPER
 * Q_HTML will set Q_STRING and Q_HTML_ESCAPE
 * Q_ALL will set Q_NULL, Q_FLOAT, Q_BOOL and Q_STRING (for use in sql queries) but NOT Q_ARRAY
 * Q_SINGLE will set quoting with single quotes instead of double quotes
 * If could not be determined, or only Q_NULL is set, fatal error is thrown
 */
function Q($value, $flags = Q_ALL) {
	
	if (is_string($flags)) return BF::gdb($flags::$db)->Q($value);
	
	if ($flags <= Q_NULL) throw new exception('Invalid flag passed to Q');
	// array and objects
	if ( is_object($value) ) return Q((array)$value, $flags);
	if ( is_array($value) ) {
		if ($flags & Q_ARRAY && $flags & Q_JSON_WRAPER) {
			$parts = array();
			//Find out if the given array is a numerical array
			$is_list = (0 !== array_reduce(array_keys($value), 'Q_callbackReduceNotAssociativeArray', 0));
		    foreach($value as $key => $val) $parts[] = ($is_list? '':'"' . $key . '":').Q($val, $flags);
			return str_replace("\n", "\\n", $is_list? '[' . implode(',',$parts) . ']' : '{' . implode(',',$parts) . '}');
		} elseif ($flags & Q_ARRAY) {
			$value = print_r($value, true);
		} else throw new exception('Calling quote on an array or object without Q_ARRAY flag set');
	}
	if ($value === NULL || $value === '') {
		if ($flags & Q_NULL) return 'null';
		elseif ($flags & Q_STRING) $value = '';	// leave for quoting
		elseif ($flags & Q_INT) return '0';
		elseif ($flags & Q_BOOL) return 'false';
		else throw new exception('Invalid flags passed to Q()');
	} elseif ( $value === TRUE || $value === FALSE || (!($flags & Q_INT) && !($flags & Q_STRING))) {
		if ($flags & Q_BOOL) return $value? 'true':'false';
		elseif ($flags & Q_INT) return $value? '1':'0';
		elseif ($flags & Q_STRING) $value = $value? '1':'0';	// leave for quoting
		else throw new exception('Invalid flags passed to Q()');
	} elseif ( (is_int($value) || is_float($value) || !($flags & Q_STRING)) && ($flags & Q_INT) ) {
		return ($flags & Q_FLOAT)? ((string)((float)$value)):((string)((int)$value));
	} elseif (!($flags & Q_STRING)) {
		throw new exception('Invalid flags passed to Q()');
	}
	return (($flags & Q_SINGLE)? '\'':'"').
		(($flags & Q_ESCAPE_HTML)? htmlspecialchars($value, ($flags & Q_SINGLE)? ENT_QUOTES : ENT_COMPAT, BF::$encoding) : addslashes($value) ).
		(($flags & Q_SINGLE)? '\'':'"');
}
/**
 * internal function needed to test whever array is assocaitive or not
 * @author : gabriel at bumpt dot nothing-here dot net (from http://php.net/is_array)
 */
function Q_callbackReduceNotAssociativeArray($a, $b) {
	return ($b === $a ? $a + 1 : 0);
}
// Define flags for quote function
define('Q_NULL', 1);			// 000000001
define('Q_BOOL', 2);			// 000000010
define('Q_INT', 4);			// 000000100
define('Q_FLOAT', 12);			// 000001100
define('Q_STRING', 16);			// 000010000
define('Q_ALL', 31);			// 000011111
define('Q_SINGLE', 32);			// 000100000
define('Q_ESCAPE_HTML', 64);		// 001000000
define('Q_HTML', 80);			// 001010000
define('Q_ARRAY', 128);			// 010000000
define('Q_JSON_WRAPER', 256);		// 100000000
define('Q_JSON', 415);			// 110011111
