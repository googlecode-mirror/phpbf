<?php

/*-------------------------------------------------------*\
|  PHP BasicFramework - Ready to use PHP site framework   |
|  License: LGPL, see LICENSE                             |
\*-------------------------------------------------------*/

/**
* Database module
* @file BF_record.php
* @package PhpBF
* @subpackage database
* @version 0.7
* @author Loic Minghetti
* @date Started on the 2008-01-09
* @comment These classes were originaly placed in framework.php and later generic.php
*/

// Security
if (!defined('C_SECURITY')) exit;


/**
 * Class that stores a list of DB record objects
 * It can be accessed like an array
 * Valid syntax includes [] operator, count, foreach, current, next, reset
 */
class BF_DB_list implements Iterator, ArrayAccess, Countable
{
	/** 
	 * @private	array	$list : List of DB row objects that have already been fetched from DB and constructed
	 */
	private $list = array();
	/**
	* @private	int 	$last_fetch : internal pointer, incremented every fetch, 0 for first row fetched
	*/
	private $last_fetch = -1;
	/**
	* @private	int 	$pointer : internal pointer for the list, or -1 if list is empty
	*/
	private $pointer = -1;
	/**
	* @private	mixed 	$resouce : DB query resource ID or statement, depending on type
	*/
	private $resource = null;
	/**
	* @private	BF_DB	$db : DB connection object that returned this list
	*/
	private $db = null;
	/**
	* @private	string	$class : DB row class in which to serve results
	*/
	private $class = null;
	/**
	* @private	int 	$length : Num of elements, -1 if not calculated yet
	*/
	private $length = -1;
	/**
	* @private	bool 	$all_fetched : True if all rows have been fetched
	*/
	private $all_fetched = false;
	
	/**
	 * Contructor
	 * @param	string	$class : Name of model of objects to be listed
	 * @param	string	$condition [optional default NULL] : SQL query condition
	 * @param	string	$extra [optional default NULL] : append some commands at the end of the SQL query (such as ORDER BY, LIMIT)
	 * @param	array	$extra_fields [optional default NULL] : Array of fields to get from DB that are not listed in data model. You may use names in the form : "tablealias.field as field", table name's will then be converted to real tables' names as defined in config file
	 */
	public function __construct($class, $condition = null, $extra = null, $extra_fields = null) {
		$this->db = BF::gdb($class::$db);
		$this->class = $class;
		
		// fields to load
		if (!is_array($class::$default_fields)) {
			// all fields
			$fields = "*";
		} else {
			$fields_array = $class::$default_fields;
			if (is_array($extra_fields)) {
				$fields_array = array_unique(array_merge($fields_array, $extra_fields));
			}
			$fields = implode(', ', $fields_array);
		}
		
		// built query
		$query_str = "SELECT ".$fields." FROM ".BF::gt($class::$table);
		if ($condition != NULL) {
			$query_str .= " WHERE ".$condition;
		}
		if ($extra != NULL) {
			$query_str .= " ".$extra;
		}
		// perfom query
		$this->resource = $this->db->query($query_str);
	}

	/**
	 * Fetch next row from db
	 */
	private function fetch() {
		if ($this->all_fetched) return false;
		// get data
		$data = $this->db->fetch_array($this->resource);
		if ($data === false) {
			$this->all_fetched = true;
			return false;
		} else {
			// create record object, save and return
			$this->last_fetch++;
			$this->list[$this->last_fetch] = new $this->class();
			return $this->list[$this->last_fetch]->load_from_array($data);
		}
	}
	/**
	 * Fetch rows until a given offset
	 */
	private function fetch_to_offset($offset) {
		while ($this->all_fetched == false && $this->last_fetch < $offset) {
			$this->fetch();
		}
		return $this->last_fetch >= $offset;
	}
	/**
	 * Return number of results
	 */
	public function count() {
		if ($this->length == -1) {
			$length = $this->db->num_rows($this->resource);
			if ($length === false) {
				// if num_rows is not available (sqlite3), then fetch all data
				while ($this->all_fetched == false) $this->fetch();
				$this->length = $this->last_fetch+1;
			} else {
				$this->length = $length;
			}
		} 
		return $this->length;
	}
	/**
	 * Reset internal pointer
	 */
	public function rewind() {
		$this->pointer = 0;
		return $this->offsetGet(0);
	}
	/**
	 * Get result at current pointer position
	 */
	public function current() {
		return $this->offsetGet($this->pointer);
	}
	/**
	 * Get row position
	 * @return	int 	Index or -1 if empty
	 */
	public function key() {
		return $this->pointer;
	}
	/**
	 * Get next entry
	 */
	public function next() {
		return $this->offsetGet(++$this->pointer);
	}
	/**
	 * Returns wether current offset is valid
	 */
	public function valid() {
		return $this->offsetExists($this->pointer);
	}
	/**
	 * Returns whether a given offset is valid
	 */
	public function offsetExists($offset) {
		$this->fetch_to_offset($offset);
		return isset($this->list[$offset]);
	}
	/**
	 * Get record object at a given offset
	 * @param	int 	$offset
	 * @return	mixed	DB record object or false if offset does not exists
	 */
	public function offsetGet($offset) {
		if ($this->offsetExists($offset)) return $this->list[$offset];
		else return false;
	}
	
	public function offsetSet($offset, $value) {throw new exception('Replacing elements from a BF_DB_list result list is not allowed');}
	public function offsetUnset($offset) {throw new exception('Removing elements from a BF_DB_list result list is not allowed');}
} 

?>
