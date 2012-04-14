<?php

/**
 * Exception to handle php errors
 * Note : The exception is catched on construction (need not be thrown) since PHP will not proceed otherwise
 */
class BF_php_exception extends exception {
	
	/**
	 * @var 	array	$context : Context array returned when an error is triggered
	 */
	public $context = Array();
	/**
	 * Constructor
	 */
	public function __construct($code, $string, $file, $line, $context) {
		parent::__construct($string, $code);die($string);
		// override line and file to match error
		$this->line = $line;
		$this->file = $file;
		// save context, in an extended propertie
		$this->context = $context;
	}
}

