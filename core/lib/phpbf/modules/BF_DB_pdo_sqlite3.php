<?php

/*-------------------------------------------------------*\
|  PHP BasicFramework - Ready to use PHP site framework   |
|  License: LGPL, see LICENSE                             |
\*-------------------------------------------------------*/

/**
* SQLite3 (based on PDO) DB module
* @file database.pdo_sqlite3.php
* @package PhpBF
* @subpackage database_pdo_sqlite3
* @version 0.7
* @author Loic Minghetti
* @date Started on the 2009-02-16
*/

BF::load_module("BF_DB");

/**
 * Class to access SQLite3 database via PDO
 */
class BF_DB_pdo_sqlite3 extends BF_DB {
	
	private	$pdo;
	private	$last_result;
	public	$num_queries = 0;

	public function __construct($db, $host = null, $user_not_used = null, $password_not_used = null) {
		try {
			$this->pdo = new PDO('sqlite:'.BF::gr($db, $host? $host : 'DB')->path());
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			throw new exception('Failed connecting to SQLite3 via PDO : '.$e->getMessage());
		}
	}
	public function close() {
		// no close method for PDO
		return false;
	}
	public function num_rows($result = null) {
		// no row count method for PDO on SELECT queries
		return false;
	}
	
	public function affected_rows($result = null) {
		if ($result == null) $result = $this->last_result;
		return $result->rowCount();
	}
	
	public function seek_row($offset, $result = null) {
		// no row seek method for PDO
		throw new exception('Seek row is no implemented for SQLite3 via PDO');
	}
	public function fetch_array($result = null) {
		if ($result == null) $result = $this->last_result;
		return $result->fetch(PDO::FETCH_ASSOC);
	}
	public function last_insert_id() {
		return $this->pdo->lastInsertId();
	}
	public function query ($query) {
		// Remove any pre-existing queries
		unset($this->last_result);
		if($query == "") return false;
		
		$this->num_queries++;
		$result = $this->last_result = $this->pdo->query($query);
		if (!$result) throw new exception('SQLite3 via PDO query error<br>Query : '.$query.'<br/>Error : '.print_r($this->pdo->errorInfo, true));
		return $result;
	}
	
	public static function supported () {
		return extension_loaded("pdo_sqlite");
	}
	
	// quoting is different in sqlite
	public function Q($val = "") {
		return $this->pdo->quote($val);
	}
}

?>
