<?php

/*-------------------------------------------------------*\
|  PHP BasicFramework - Ready to use PHP site framework   |
|  License: LGPL, see LICENSE                             |
\*-------------------------------------------------------*/

/**
* User module (default functionalities)
* @file user.php
* @package PhpBF
* @version 0.5
* @author Loic Minghetti
* @date Started on the 2007-08-18
*/

// Security
if (!defined('C_SECURITY')) exit;

BF::load_module('BF_record');

/**
 * Class to deal with users
 */
abstract class user_default extends BF_record {
	
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
			return $negative xor $this->_logged;
		// if condition is for group name
		} elseif (strncmp($condition, 'g:', 2) == 0) {
			switch (BF::gc('user_group')) {
				case 1 : return $negative xor ($this->_groups & pow(2, BF::gc('groups_id', substr($condition, 2))-1));
				case 2 :  throw new exception ('Database user groups not implemented yet');
				default : return false;
			}
		// if condition is for group id
		} elseif (strncmp($condition, 'gid:', 4) == 0) {
			switch (BF::gc('user_group')) {
				case 1 : return $negative xor ($this->_groups & pow(2, substr($condition, 4)-1));
				case 2 : throw new exception ('Database user groups not implemented yet');
				default : return false;
			}
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
	
	
	// overloading for some common properties
	// This are "hidden" attributes and are accessible using a _ (eg. $user->_logged)
	
	protected function __get__logged() {
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
	protected function __get__username() {
		return $this->_data['_username'] = $this->get(BF::gc('user_field_username'));
	}
	protected function __get__groups() {
		switch (BF::gc('user_group')) {
			case 1 : 
				try {
					return $this->_data['_groups'] = BF::gc('groups_ancestors', $this->get(BF::gc('user_field_id_group')));
				} catch(exception $e) {
					return 0;
				}
			case 2 : 
				throw new exception ('Database user groups not implemented yet');
				break;
			default : return false;
		}
	}
	protected function __get__email() {
		return $this->_data['_email'] = $this->get(BF::gc('user_field_email'));
	}
	protected function __get__password() {
		return $this->_data['_password'] = $this->get(BF::gc('user_field_password'));
	}
	protected function __set__password($value) {
		return $this->_data['_password'] = $this->set(BF::gc('user_field_password'), $value);
	}
	protected function __get__date_login() {
		if (!BF::gc('user_field_date_login')) return false;
		return $this->_data['_date_login'] = $this->get(BF::gc('user_field_date_login'));
	}
	protected function __set__date_login($value) {
		if (!BF::gc('user_field_date_login')) return false;
		return $this->_data['_date_login'] = $this->set(BF::gc('user_field_date_login'), $value);
	}
}



/**
 * List users that match condition
 * @param	string	$condition [optional default NULL] : Query condition. Incompatible with file DB
 * @param	string	$extra [optional default NULL] : append some commands at the end of the SQL query (such as ORDER BY, LIMIT)
 * @return	array[user object]
*/
function list_users ($condition = NULL, $extra = NULL) {
	return BF::gdb(BF::gc('tables', 'users', 0))->get('users', $condition, $extra, NULL, "user");
}

/**
 * Count users that match condition
 * @param	string	$condition [optional default NULL] : Query condition. Incompatible with file DB
 * @param	string	$extra [optional default NULL] : append some commands at the end of the SQL query (such as GROUP BY)
 * @return	int
*/
function count_users ($condition = NULL, $extra = NULL) {
	return BF::gdb(BF::gc('tables', 'users', 0))->count('users', $condition, $extra);
}

/**
 * Function to check if a user is logged in and return a user object of the logged user
 * @return	user object or false if no user session could be found
 */
function BF_get_logged_user() {
	
	if (!isset($_SESSION['BF_'.BF::gc('project_id').'_logged_id_user'])) return false;
	
	$user = new user($_SESSION['BF_'.BF::gc('project_id').'_logged_id_user']);
	if (!$user->_logged) return false;
	
	return $user;
}


?>
