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
	// get dispo for a given day, if not exists, returns empty dispo object
	public function get_dispo($day) {
		BF::load_model("dispo");
		$dispos = BF::glist("dispo", "id_journee = ".Q($day, "dispo")." AND username_user = ".Q($this->username, "dispo"));
		if (count($dispos) == 1) return $dispos[0];
		else {
			$dispo = new dispo();
			$dispo->id_journee = $day;
			$dispo->username_user = $this->username;
			return $dispo;
		}
	}
	// get array of dispos for the week of a given day, if not exists, returns array of empty dispo objects
	public function get_week_dispos($day) {
		BF::load_model("dispo");
		$monday = $day - date("N", $day*3600*24+3600*12) + 1;
		
		$result = BF::glist("dispo", "id_journee BETWEEN ".Q($monday, "dispo")." AND ".Q($monday+6, "dispo")." AND username_user = ".Q($this->username, "dispo"), "ORDER BY id_journee");
		return $result;
	}
	
	// get the number of days from the last working day
	public function get_duration_last_work() {
		if ($this->date_last_work == 0)
			return -1;
			
		$result = floor((BF::$time - $this->date_last_work)/(24*3600));
		return $result;
	}
	
	
	// GROUPS
	protected function __get_group() {
		BF::load_model("group");
		$this->_data['group'] = new group($this->id_group);
		return $this->_data['group'];
	}
	protected function __get_groups() {
		BF::load_model("group");
		$this->_data['groups'] = array();
		for ($i = $this->id_group; $i >= 1; $i--) {
			$this->_data['groups'][] = new group($i);
		}
		return $this->_data['groups'];
	}
	public function has_group_name($name) {
		foreach ($this->groups as $group) {
			if ($group->name === $name) return true;
		}
		return false;
	}
	public function has_group_id($id) {
		foreach ($this->groups as $group) {
			if ($group->id === $id) return true;
		}
		return false;
	}
}






//test si un ordre etudiant est valide
function ordre_etudiant_valide($ordre) {

	if ($ordre == null) return false;

	$etudiants = BF::glist("user", 'id_group = 2');
	$etudiants_ordre = explode('/',$ordre);

	foreach ($etudiants as $e)
	if (!in_array($e->username, $etudiants_ordre))
	    return false;
	return true;
}

//renvoit un ordre etudiant au hasard
function get_ordre_etudiant() {
	$etudiants = array();
	foreach (BF::glist("user", 'id_group = 2') as $etudiant) {
		$etudiants[] = $etudiant->username;
	}
	shuffle($etudiants);
	return implode('/',$etudiants);
}
		




