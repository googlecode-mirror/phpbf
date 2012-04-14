<?php

/*-------------------------------------------------------*\
|  PHP BasicFramework - Ready to use PHP site framework   |
|  License: LGPL, see LICENSE                             |
\*-------------------------------------------------------*/

/**
* User module (Extends default user module)
* @file user.php
* @package PhpBF
* @version 0.5
* @author Loic Minghetti
* @date Started on the 2007-08-18
*/

// Security
if (!defined('C_SECURITY')) exit;

BF::load_module('user.default');

/**
 * Class to deal with users
 */
class user extends user_default {
	
	/**
	 * Built a user active record object
	 * @param	int	$id [optional default NULL] : Value of the ID field
	 * @param 	array	$extra_fields [optional default NULL] : Add some extra fields not defined in the config file
	 */
	public function __construct ($id = NULL, $extra_fields = NULL) {
		BF_record::__construct($id, 'users', $extra_fields);
	}
	/**
	 * Load user data by giving it's username
	 * @param	string	$username : Load user given value of the username field
	 */
	public function load_by_username($username) {
		$result = $this->_db->get_first($this->_table, BF::gc('user_field_username')." = ".Q($username));
		if ($result == false) return false;
		return $this->load_from_array($result);
	}
	
	protected function __get_username() {
		return utf8_encode($this->_data["login"]);
	}
	protected function __get_coms() {
		$coms = list_coms("mId = ".q($this->mId));
		return $this->_data["coms"] = $coms;
	}
	public function in_com($com) {
		return in_array($com, $this->coms);
	}
	public function is_admin() {
		return $this->in_com("bural") || $this->in_com("humain");
	}
	

	
	/**
	 * Login the user
	 * @param	string	$password : Password submited by user (to check it matches the md5 hash in the db)
	 * @param	string	$force [optional default false] : Force login, even if password doesn't match
	 * @return	bool	true
	 */
	public function login($password, $force = false) {	
		if (!$force && md5($password) != $this->_password) return false;
		
		parent::register();
		
		if (isset($this->locale)) BF::gl()->set($this->locale);
		$this->_date_login = BF::$time;
		$this->save();
		return true;
	}
	/**
	 * Logout user
	 */
	public function logout() {
		parent::unregister();
	}

	/**
	 * Reset user password
	 * Set a new 6 char password and return it
	 * @return	string	New password
	 */
	public function reset_password() {
		$password = "";
		$leters = "abcdefghjkmnpqrstuvwxyz123456789";
		for ($i = 0; $i < 6; $i++) {
			$password .= $leters{rand(0,strlen($leters)-1)};
		}
		$this->_password = md5($password);
		$this->save();
		return $password;
	}
	
	/**
	 * Reset user password and send by email
	 */
	public function send_password() {
		BF::load_module('mail');
		
		$tpl = BF::ouput('template');
		$user =& $this;
		$tpl->assign('user', $user);
		$tpl->assign('password', $this->reset_password());

		$mail = new mailer();
		$mail->From = BF::gu()->_email;
		$mail->FromName = BF::gu()->_username;
		$mail->AddReplyTo($mail->From, $mail->FromName);
		$mail->AddAddress($this->_email);
		$mail->Body = $tpl->load('mail_user_password')->disp(false);
		$mail->Subject = $tpl->get_template_vars('subject');
		
		if ($mail->send()) return true;
		else throw new exception('Failed sending password mail');
	}
	// get group name of user
	protected function __get_group_name() {
		try {
			$this->_data['group_name'] = ucwords(BF::gc("groups", $this->id_group));
		} catch (exception $e) {
			return false;
		}
		return $this->_data['group_name'];
	}
	
	
	////////////////////////////////////////////////
	
	/**
	 * Load user data by giving it's google id
	 * @param	string	$google_id
	 */
	public function load_by_google_id($google_id) {
		$result = $this->_db->get_first($this->_table, "google_id = ".Q($google_id));
		if ($result == false) return false;
		return $this->load_from_array($result);
	}
	/**
	 * Load user data by giving it's email
	 * @param	string	$email
	 */
	public function load_by_email($email) {
		$result = $this->_db->get_first($this->_table, "mail = ".Q($email));
		if ($result == false) return false;
		return $this->load_from_array($result);
	}
}


function list_coms ($condition = NULL, $extra = "ORDER BY coms ASC") {
	$results = BF::gdb(BF::gc('tables', 'coms', 0))->get_query('SELECT DISTINCT coms FROM '.BF::gc('tables', 'coms', 1).($condition == NULL ? "":" WHERE ".$condition)." ".$extra);
	
	return array_map(function ($row) { return $row['coms']; }, $results);
}


