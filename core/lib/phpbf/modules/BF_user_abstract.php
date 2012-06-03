<?php

/**
* @file BF_user_abstract.php
*/

BF::load_module('BF_record');

abstract class BF_user_abstract extends BF_record {

	/**
	 * Function to check if a user is logged in and return a user object of the logged user
	 * @return	user object or false if no user session could be found
	 * @warning	BF:gu() will  call this function, prefere using BF::gu() to get currently logged user
	 */
	static public function get_logged_user() {
	
		if (!isset($_SESSION['BF_'.BF::gc('project_id').'_logged_id_user'])) return false;
	
		$user = new user($_SESSION['BF_'.BF::gc('project_id').'_logged_id_user']);
		if (!$user->is_logged()) return false;
	
		return $user;
	}
	
	
	/**
	 * Load user data by giving it's username
	 * @param	string	$username : Load user given value of the username field
	 */
	static public function get_by_username($username) {
		$result = BF::glist("user", user::$username_field." = ".Q($username, "user"));
		if (count($result) != 1) return false;
		$user = new user();
		$user->load_from_array($result[0]->_data);
		return $user;
	}
	
	
	/**
	 * Login the user
	 * @param	string	$password : Password submited by user
	 * @param	string	$force [optional default false] : Force login, even if password doesn't match
	 * @return	bool	true
	 */
	public function login($password, $force = false) {	
		if (!$force && !$this->check_password($password)) return false;
		
		$this->register();
		
		if (isset($this->locale)) BF::gl()->set($this->locale);
		$this->set_login_time(BF::$time);

		return true;
	}
	
	
	/**
	 * Logout user
	 */
	public function logout() {
		$this->unregister();
	}
	

	/**
	 * register the user (once loged in)
	 */
	protected function register() {
		$_SESSION['BF_'.BF::gc('project_id').'_login_time'] = BF::$time;
		$_SESSION['BF_'.BF::gc('project_id').'_logged_id_user'] = $this->_id;
		if (BF::gc('user_inactivity_time') != 0) $_SESSION['BF_'.BF::gc('project_id').'_activity_time'] = BF::$time;
		
		// set current user
		BF::gu($this);
		
		// clear all cached data
		$this->_data = array();
	}
	/**
	 * Logout user
	 */
	protected function unregister() {
		unset($_SESSION['BF_'.BF::gc('project_id').'_login_time']);
		unset($_SESSION['BF_'.BF::gc('project_id').'_logged_id_user']);
		unset($_SESSION['BF_'.BF::gc('project_id').'_activity_time']);
		
		// erase the global var pointing to this object
		BF::gu(false);
		
		// clear all cached data
		$this->_data = array();
	}
	
	/**
	 * Match user against a single access string condition
	 * @param	array	$condition : Condition to check. See doc on syntax
	 * @return	bool	true if match, false otherwise
	 */
	public function ga_condition($condition) {
		// check for a - sign at the beggining
		$negative = strlen($condition) >= 1 && $condition[0] == '-';
		if ($negative) $condition = substr($condition, 1);
		// if null condition
		if ($condition === 0 || $condition === '0' || $condition === "" || $condition === NULL) {
			return !$negative;
		// if checking that user is currently logged
		} elseif ($condition == "1") {
			return $negative xor $this->is_logged();
		// if condition is for group name
		} elseif (strncmp($condition, 'g:', 2) == 0) {
			return $negative xor $this->has_group_name(substr($condition, 2));
		// if condition is for group id
		} elseif (strncmp($condition, 'gid:', 4) == 0) {
			return $negative xor $this->has_group_id(substr($condition, 4));
		// if condition is user name
		} elseif (strncmp($condition, 'u:', 2) == 0) {
			return $negative xor $this->_username == substr($condition, 2);
		// if condition is user is
		} elseif (strncmp($condition, 'uid:', 4) == 0) {
			return $negative xor $this->_id == substr($condition, 4);
		} elseif ($condition == null) {
			return !$negative;
		} else {
			throw new exception('Unable to parse access string condition : '.$condition);
		}
	}
	
	
	public function is_logged() {
		if (isset($this->_data['_logged'])) return $this->_data['_logged'];
		
		if (
			isset($_SESSION['BF_'.BF::gc('project_id').'_login_time'], $_SESSION['BF_'.BF::gc('project_id').'_logged_id_user'])
			&& $_SESSION['BF_'.BF::gc('project_id').'_logged_id_user'] == $this->_id
			&& ( BF::gc('user_login_time') == 0 || $_SESSION['BF_'.BF::gc('project_id').'_login_time'] > BF::$time - 3600*BF::gc('user_login_time'))
			&& ( BF::gc('user_inactivity_time') == 0 || ( isset($_SESSION['BF_'.BF::gc('project_id').'_activity_time']) && $_SESSION['BF_'.BF::gc('project_id').'_activity_time'] > BF::$time - 3600*BF::gc('user_inactivity_time')) )
		) {
			return $this->_data['_logged'] = true;
		} else {
			if (isset($_SESSION['BF_'.BF::gc('project_id').'_logged_id_user'])) unset($_SESSION['BF_'.BF::gc('project_id').'_logged_id_user']);
			return $this->_data['_logged'] = false;
		}
	}
}




?>
