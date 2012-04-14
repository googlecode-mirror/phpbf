<?php

/*-------------------------------------------------------*\
|  PhpBF                                                  |
|  License: LGPL, see LICENSE                             |
\*-------------------------------------------------------*/

/**
* MySQL DB module
* @file BF_DB_mysql.php
* @package PhpBF
* @subpackage database_mysql
* @version 0.7
* @author Loic Minghetti
* @date Started on the 2007-08-18
*/

BF::load_module("BF_DB");

/**
 * Class to access mysql database
 */
class BF_DB_mysql extends BF_DB {
	
	private	$link;
	private	$last_result;
	public	$num_queries = 0;

	public function __construct($db, $host = null, $user = null, $password = null) {
		$this->link = @mysql_pconnect($host, $user, $password);
		if($this->link) {
			if(!@mysql_select_db($db)) {
				@mysql_close($this->link);
				$this->link = NULL;
				throw new exception('Mysql was not able to select DB : '.$db);
			}
		} else {
			throw new exception('Unable to connect to mysql server');
		}
	}
	public function close() {
		if (!$this->link) return false;
		if($this->last_result) @mysql_free_result($this->last_result);
		return @mysql_close($this->link);
	}
	/* NOT DECLARED IN INTERFACE */
	public function free_result($result = null) {
		if ($result == null) $result = $this->last_result;
		return @mysql_free_result($this->query_result);
	}
	public function num_rows($result = null) {
		if ($result == null) $result = $this->last_result;
		return @mysql_num_rows($result);
	}
	
	public function affected_rows($result = null) {
		if ($result == null) $result = $this->last_result;
		return @mysql_affected_rows($result);
	}
	
	public function seek_row($offset, $result = null) {
		if ($result == null) $result = $this->last_result;
		if (!mysql_data_seek($result, $offset)) throw new exception ("Cannot seek to row ".$offset.": " . mysql_error($this->link) . "\n");
		return @mysql_fetch_assoc($result);
	}
	public function fetch_array($result = null) {
		if ($result == null) $result = $this->last_result;
		return @mysql_fetch_assoc($result);
	}
	public function last_insert_id() {
		return @mysql_insert_id($this->link);
	}
	
	public function query ($query) {
		// Remove any pre-existing queries
		unset($this->last_result);
		if($query == "") return false;
		
		$this->num_queries++;
		$result = $this->last_result = mysql_query($query, $this->link);
		if (!$result) throw new exception('Mysql query error<br>Query : '.$query.'<br/>Error : '.mysql_error($this->link));
		return $result;
	}
	
	public static function supported () {
		return extension_loaded("mysql");
	}
}

?>
