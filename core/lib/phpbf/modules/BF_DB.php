<?php

/*-------------------------------------------------------*\
|  PHP BasicFramework - Ready to use PHP site framework   |
|  License: LGPL, see LICENSE                             |
\*-------------------------------------------------------*/

/**
* Database module
* @file BF_DB_inerface.php
* @package PhpBF
* @subpackage database
* @version 0.7
* @author Loic Minghetti
* @date Started on the 2008-01-09
* @comment These classes were originaly placed in framework.php and later generic.php
*/

BF::load_module("BF_DB_interface");

abstract class BF_DB implements BF_DB_interface {
	public function get_query ($query) {
		$result = $this->query($query);
		$rows = array();
		while($row = $this->fetch_array($result)) {
			$rows[] = $row;
		}
		return $rows;
	}
	
	public function get_first ($query) {
		$result = $this->get_query($query);
		if (count($result) == 0) return false;
		return $result[0];
	}
	
	public function get_field($query, $field = null) {
		$rows = $this->get_query($query);
		//process result
		return array_map(create_function('$x', 'return $x[\'field\'];'), $rows);
	}

	public function count ($table, $condition = NULL, $extra = NULL) {
		// built query with or without any conditions
		$query_str = "SELECT count(*) as num FROM ".$table;
		if ($condition != null) $query_str .= " WHERE ".$condition;
		// add extra
		if ($extra != NULL) $query_str .= " ".$extra;
		// perform query
		$rows = $this->get_query($query_str);
		//process result
		if (count($rows) != 1) throw new exception('Count query failed : '.$query_str);
		return $rows[0]['num'];
	}
	
	public function set ($table, $condition, $fields, $force = false) {
		if ($force == true) {
			switch ($this->count($table, $condition)) {
				case 0 : return $this->add($table, $fields);break;
				case 1 : break;
				default : throw new exception('The $force parameter may not be set to true if there are more than one entry matching condition in the table. Condition : '.$condition, __FILE__,__LINE__);
			}
		}
		$query_str = "UPDATE ".$table." SET ";
		if (!is_array($fields)) throw new exception('List of fields to update must be an array');
		foreach ($fields as $key => $val) {
			$query_str .= $key." = ".$this->Q($val).", ";
		}
		$query_str = substr($query_str, 0, -2)." WHERE ".$condition;
		return $this->query($query_str);
	}
	
	public function add ($table, $fields) {
		if (!is_array($fields)) throw new exception('List of fields to add must be an array');
		$query_str = "INSERT INTO ".$table." (".implode(', ', array_keys($fields)).") VALUES (".implode(', ', array_map(array($this, 'Q'), $fields)).")";
		$this->query($query_str);
		return $this->last_insert_id();
	}
	
	public function add_multiple ($table, $columns, $entries) {
		if (count($columns) == 0) throw new exception('To add multiple entries you must have at least one columns definied');
		if (count($entries) == 0) return false;
		
		$query_str = "INSERT INTO ".$table." (".implode(', ', $columns).") VALUES";
		
		$query_entries = Array();
		foreach ($entries as $entry) {
			if (!is_array($entry)) throw new exception('In multiple add, each entries must be an array of value');
			$query_entries[] = '('.implode(', ', array_map('Q', $entry)).')';
		}
		$query_str .= implode(', ', $query_entries);
		$this->query($query_str);
		return $this->last_insert_id();
	}
	
	public function del ($table, $condition) {
		$this->query("DELETE FROM ".$table." WHERE ".$condition);
		return $this->affected_rows() >= 1;
	}
	
	public function Q($val = "") {
		return Q($val);
	}
}


?>
