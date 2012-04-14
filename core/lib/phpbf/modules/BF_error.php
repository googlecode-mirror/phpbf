<?php

/*-------------------------------------------------------*\
|  PHP BasicFramework - Ready to use PHP site framework   |
|  License: LGPL, see LICENSE                             |
\*-------------------------------------------------------*/

/**
* Error module
* @file error.php
* @package PhpBF
* @subpackage error
* @version 0.4
* @author Loic Minghetti
* @date Started on the 2004-02-27
*/

// Security
if (!defined('C_SECURITY')) exit;


class BF_error {

	private $e;
	private $debug_html = false;
	private $display = array();
	
	public function __construct($e) {
		$this->e = $e;
		if (!$e instanceof BF_user_exception) {
			// log error and send mail if requiered
			BF_error::log($e);
		}
		
		// array of infos to display to user
		try {
			// if we are dealing with a user error
			if ($e instanceof BF_user_exception) {
				$this->display['type'] = get_class($e);
				$this->display['title'] = BF::gl()->tl($this->e->display['title']? $this->e->display['title'] : '[xx:error.title_'.$this->display['type'].']') ;
				$this->display['message'] = BF::gl()->tl($this->e->display['message']? $this->e->display['message'] : '[xx:error.'.$this->display['type'].']') ;

			// otherwise if a php error (bug)
			} else {
				$this->display['title'] = BF::gl()->tl_tag("error.title_BF_internal");
				$this->display['message'] = BF::gl()->tl_tag("error.BF_internal");
				$this->display['type'] = 'BF_internal';
			}
		} catch (exception $new_e) {
			// if a second exception occurs while translating, then just skip this step
			$this->display['title'] = 'Error occured';
			$this->display['message'] = 'Internal Error';
			$this->display['type'] = 'BF_internal';
		}
		
	}
	
	
	public function display($callback = null) {
		// clean buffer in case a buffer was active
		@ob_end_clean();
		
		try {
			if ($callback == null) {
				BF::load_module("BF_output_template");
				$tpl = new BF_output_template();
				$callback = array($tpl, "show_error");
			}
			call_user_func ($callback, $this->display['type'], $this->display['message'], $this->display['title'], BF::gc('error_debug')? $this->debug_html:null);
			
		} catch (exception $new_e) {
			// if fails, then just show a basic html page
			@header('Content-type: text/html');
			print "<html><body><div><h1>".$this->display['title']."</h1><br/>".$this->display['message'].($this->debug_html && BF::gc('error_debug')? "<br/><br/><pre>".$this->debug_html."</pre>":"")."</div></body></html>";
			die();
		}
		
	}
	
	
	public function log () {
		// timestamp for the error entry
		$dt = date("Y-m-d H:i:s (T)", BF::$time);
	
		// define an assoc array of error string
		// in reality the only entries we should
		// consider are E_WARNING, E_NOTICE, E_USER_ERROR,
		// E_USER_WARNING and E_USER_NOTICE
		$errortype = array (
			E_WARNING            => 'Warning',
			E_NOTICE             => 'Notice',
			E_USER_ERROR         => 'User Error',
			E_USER_WARNING       => 'User Warning',
			E_USER_NOTICE        => 'User Notice',
			E_STRICT             => 'Runtime Notice'
		);
		if (defined('E_RECOVERABLE_ERROR')) $errortype[E_RECOVERABLE_ERROR] = 'Catchable Fatal Error';

		$this->debug_html = "<b><u>Debug Info</u></b>\n\n";
		$this->debug_html .= "Date time : <b>" . $dt . "</b>\n";
		$this->debug_html .= "Error num : <b>" . $this->e->getCode() . "</b>\n";
		$this->debug_html .= "Error type : <b>" . (isset($errortype[$this->e->getCode()])? $errortype[$this->e->getCode()]: '-') . "</b>\n";
		$this->debug_html .= "Error msg : <b>" . $this->e->getMessage() . "</b>\n";
		$this->debug_html .= "Script name : <b>" . $this->e->getFile() . "</b>\n";
		$this->debug_html .= "Script line num : <b>" . $this->e->getLine() . "</b>\n";
		$this->debug_html .= "Trace : \n\t" . str_replace("\n","\n\t",htmlentities($this->e->getTraceAsString()));
	
		$debug_xml = "<errorentry>\n";
		$debug_xml .= "\t<datetime>" . $dt . "</datetime>\n";
		$debug_xml .= "\t<errornum>" . $this->e->getCode() . "</errornum>\n";
		$debug_xml .= "\t<errortype>" . (isset($errortype[$this->e->getCode()])? $errortype[$this->e->getCode()]: '-') . "</errortype>\n";
		$debug_xml .= "\t<errormsg>" . $this->e->getMessage() . "</errormsg>\n";
		$debug_xml .= "\t<scriptname>" . $this->e->getFile() . "</scriptname>\n";
		$debug_xml .= "\t<scriptlinenum>" . $this->e->getLine() . "</scriptlinenum>\n";
		$debug_xml .= "\t<trace><entry>" . implode('</entry><entry>', explode("\n", $this->e->getTraceAsString())) . "</entry></trace>\n";
		$debug_xml .= "</errorentry>\n\n";
	
	
		// send error mail
		try {
			if (BF::gc('error_send_mail')) {
				if (!BF::gc('error_email') || !@mail(BF::gc('error_email'),'Critical error on '.$_SERVER['HTTP_HOST'], $debug_xml, 'From: Error Handler')) {
					// if failed sending mail, add a notice to the log
					$this->debug_html .= "\nFailed sending error mail to ".BF::gc('error_email');
					$debug_xml .= "<errorentry>\n";
					$debug_xml .= "\t<datetime>" . $dt . "</datetime>\n";
					$debug_xml .= "\t<errormsg>Failed sending error mail to ".BF::gc('error_email')."</errormsg>\n";
					$debug_xml .= "</errorentry>\n\n";
				}
			}
		} catch(exception $new_e) {
		}
	
		// log error
		if (BF::gc('error_log')) {
			@error_log($this->debug_xml, 3, BF::gf('log')->path().'errorlog.txt');
		}
	}

}


?>
