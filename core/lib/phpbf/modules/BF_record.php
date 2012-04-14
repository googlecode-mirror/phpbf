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
 * Active Record object for rows returned from database. 
 * An object that wraps a row in a database table or view, encapsulates the database access, and adds domain logic on that data.
 * All Active Records should inherite from this class so that they can work with their database
 */
abstract class BF_record {


	/**
	 * The alias of the table holding the object's data (can be multiple tables in an array)
	 * @var 	mixed 
	 */
	static public $table;
	/**
	 * Array of fields to load by default on first select query to db. Set to null for all fields.
	 * @var 	array/null
	 */
	static public $default_fields;
	/**
	 * Name of database field containing the unique identifer
	 * @var 	string
	 */
	static public $id_field;
	/**
	 * name of DB connection (as defined in config file)
	 * @var		string
	 */
	static public $db;



	
	/**
	 * Name of theactive record class
	 * @var 	string
	 */
	public $_class = NULL;
	
	/**
	 * A unique identifer for the object
	 * @var 	mixed
	 */
	public $_id = NULL;
	
	/**
	 * Array containing the object's properties as retrived from the database
	 * NULL if not retrived yet
	 * @var 	array[mixed]
	 */
	protected $_data = NULL;
	
	/**
	 * Array containing the object's modified properties before thay are saved into the database
	 * @var 	array[mixed]
	 */
	protected $_data_mod = Array();
	
	/**
	 * DB connection object
	 * @var		db object
	 */
	protected $_db = NULL;
	
	/**
	 * Extra fields to load but not defined in the config file
	 * @var		array[string]
	 */
	protected $_extra_fields = array();
	
	/**
	 * Contructor
	 * @param 	string 		$class : Name of the table holding the object's data
	 * @param	int		$id [optional default NULL] : Identifier of the element
	 * @return 	void
	 */
	public function __construct($id = NULL)
	{
		$class = get_class($this);
		$this->_class = $class;
		$this->_db = BF::gdb($class::$db);
		$this->_id = $id;
	}
	
	/**
	 * Destructor
	 * @return void
	 */
	public function __destruct()
	{
		// save modified properties if any
		//$this->save();	// Is this realy a good idea?
	}
	
	public function __isset($field)
	{
		return ($this->load() && isset($this->_data[$field]));
	}
	public function __get($field)
	{
		return $this->get($field);
	}
	public function __set($field, $value)
	{
		return $this->set($field, $value);
	}
	
	/**
	 * Test if object (with the given ID) exists or not in the database
	 * @return true if exists, false otherwise
	 */
	public function exists()
	{
		if ($this->_id == null) return false;
		return $this->load();
	}
	
	/**
	 * Load objects properties from db
	 * @param	bool	$reload [optional default false] : Force a reload from the DB
	 * @return true on success, false otherwise
	 */
	public function load($reload = false)
	{
		if ($reload || $this->_data === NULL) {
			$class = $this->_class;
			
			// fields to load
			if (!is_array($class::$default_fields)) {
				// all fields
				$fields = "*";
			} else {
				$fields_array = $class::$default_fields;
				if (count($this->_extra_fields)>0) {
					$fields_array = array_unique(array_merge($fields_array, $this->_extra_fields));
				}
				$fields = implode(', ', $fields_array);
			}
			// perfom query
			$data = $this->_db->get_first("SELECT ".$fields." FROM ".BF::gt($class::$table)." WHERE ".$class::$id_field." = ".$this->_db->Q($this->_id));
			if (is_array($data)) {
				$this->load_from_array($data);
			}
		}
		return is_array($this->_data);
	}
	/**
	 * Load objects properties from an array
	 * @param	array	$data : Array of preperties
	 * @return true on success, false otherwise
	 */
	public function load_from_array($data)
	{
		$this->_data = $data;
		$class = $this->_class;
		if (isset($this->_data[$class::$id_field])) {
			if ($this->_id != null && $this->_id != $this->_data[$class::$id_field]) throw new BF_exception("ID mismatch when loading from array.");
			$this->_id = $this->_data[$class::$id_field];
		}
		return true;
	}
	
	/**
	 * Save object's modified properties to the db
	 * @param	bool	$force [optional default false] : If no row match ID, then add an entry in the table. 
	 * @warning Object's id and table need to be set
	 * @return true on success, false otherwise
	 */
	public function save($force = false)
	{
		$class = $this->_class;
		if (is_array($class::$table)) throw new BF_exception("Active records coming from multiple tables cannot be updated.");
		
		if ($force == true && ($this->_id == null || !$this->exists())) return $this->add();
		elseif ($this->_id == null) return false;
		
		if (count($this->_data_mod) == 0) return true;		
		
		if ($this->_db->set($class::$table, $class::$id_field.' = '.$this->_db->Q($this->_id), $this->_data_mod))
		{
			$this->_data = is_array($this->_data)? array_merge($this->_data, $this->_data_mod) : $this->_data_mod;
			$this->_id = $this->_data[$class::$id_field];
			$this->_data_mod = array();
			return true;
		} else return false;
	}
	
	/**
	 * Get an object's property. Load will be called if data have not yet been loaded
	 * @param 	string	$field : Name of the field of the property
	 * @return 	string
	 */
	public function get($field)
	{
		$class = $this->_class;
		if ($this->_data === NULL) {
			$this->load();
		}
		
		// see if it is not already loaded
		if ($this->_data === false || !array_key_exists($field, $this->_data)) {
			// see if there is custum function to handle this
			if (method_exists($this, '__get_'.$field)) {
				return call_user_func(Array($this, '__get_'.$field));
			} elseif ($this->_data === false) {
				return null;
			} else {
				$this->add_field($field);
				$this->load(true);
				if (!array_key_exists($field, $this->_data)) {
					return null;
				}
			}
		}
		return $this->_data[$field];
	}
	/**
	 * Set an object's property
	 * @param 	mixed	$field : Name of the field of the property, or associative array of field => value
	 * @param 	string	$value : Value to insert, or null if $field is array
	 * @warning Object needs to be saved for changes to be applied
	 * @warning Object's table needs to be set
	 * @return 	bool
	 */
	public function set ($field, $value = null)
	{
		$class = $this->_class;
		if ($class::$table == NULL) return false;
		if (is_array($field)) {
			if ($value !== null) throw new exception('Value must be set to NULL when field is an array');
			foreach ($field as $key => $val) {
				// see if there is custum function to handle this
				if (method_exists($this, '__set_'.$field)) 
					return call_user_func(Array($this, '__set_'.$field), $value);
				$this->_data_mod[$key] = $val;
			}
		} else {
			if (method_exists($this, '__set_'.$field)) 
				return call_user_func(Array($this, '__set_'.$field), $value);
			$this->_data_mod[$field] = $value;
		}
	}
	/**
	 * Adds current object to DB
	 * If object's ID is NOT specified, then will try to use auto incrementation, and will return new ID
	 * @warning Object's table needs to be set
	 * @return	int : ID of inserted object
	 */
	public function add ()
	{
		$class = $this->_class;
		if ($class::$table == NULL) return false;
		$new_id = $this->_db->add($class::$table, $this->_data_mod);
		if ($new_id)
		{
			$this->_data = $this->_data_mod;
			if (!isset($this->_data['id'])) $this->_id = $this->_data['id'] = $new_id;
			$this->_data_mod = Array();
			return $new_id;
		} else {
			$this->_data = false;
			return false;
		}
	}
	/**
	 * Remove object from DB
	 * @return true on success, false otherwise
	 */
	public function del()
	{
		$class = $this->_class;
		if ($class::$table == NULL) return false;
		if ($this->_db->del($class::$table, $class::$id_field.' = '.$this->_db->Q($this->_id))) {
			$this->_data_mod = Array();
			return true;
		} else return false;
	}
	
	/**
	 * Add an extra field to be loaded on firt query
	 * @param	string $field : Name of field to be added
	 */
	public function add_field($field) {
		$this->_extra_fields[] = $field;
	}  
}

?>
