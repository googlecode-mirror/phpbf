<?php

/**
* @file user.php
*/

BF::load_module('BF_user_abstract');

class user extends BF_user_abstract {
	static public $table = "users";
	static public $id_field = "username";
	static public $username_field = "username";
	static public $default_fields = array("username","password_md5","date_login","id_group","email", "telephone", "matricule", "name", "date_last_work");
	static public $db = "db";
	static public function get_logged_user() {
		return BF_user_abstract::get_logged_user();
	}
	public function check_password($password) {
		return md5($password) == $this->password_md5;
	}
	public function set_login_time($time) {
		$this->date_login = $time;
		$this->save();
	}
	// GROUPS
	protected function __get_group() {
		/** 
		 * To be customized
		 * Example : 
		 
		BF::load_model("group");
		$this->_data['group'] = new group($this->id_group);
		return $this->_data['group'];
		
		*/
	}
	protected function __get_groups() {
		/** 
		 * To be customized
		 * Example : 
		 
		BF::load_model("group");
		$this->_data['groups'] = array();
		for ($i = $this->id_group; $i >= 1; $i--) {
			$this->_data['groups'][] = new group($i);
		}
		return $this->_data['groups'];
		
		*/
	}
	public function has_group_name($name) {
		/** 
		 * To be customized
		 * Example : 
		 
		foreach ($this->groups as $group) {
			if ($group->name === $name) return true;
		}
		return false;
		
		*/
	}
	public function has_group_id($id) {
		/** 
		 * To be customized
		 * Example : 
		 
		foreach ($this->groups as $group) {
			if ($group->id === $id) return true;
		}
		return false;
		
		*/
	}
	
	
	


	/**
	 * Reset user password
	 * Set a new 6 char password and return it
	 * @return	string	New password
	 */
	public function reset_password() {
		/**
		 * Example 
		 */
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
		/**
		 * Example 
		 */
		BF::load_module('BF_mail');
		BF::load_module("BF_output_template");
		
		$tpl = new BF_output_template('mail_user_password');
		
		$user =& $this;
		$tpl->assign('user', $user);
		$tpl->assign('password', $this->reset_password());

		$mail = new BF_mail();
		$mail->From = BF::gu()->_email;
		$mail->FromName = BF::gu()->_username;
		$mail->AddReplyTo($mail->From, $mail->FromName);
		$mail->AddAddress($this->_email);
		$mail->Body = $tpl->disp(false);
		$mail->Subject = $tpl->get_template_vars('subject');
		
		if ($mail->send()) return true;
		else throw new exception('Failed sending password mail');
	}
}


