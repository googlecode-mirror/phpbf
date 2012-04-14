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
 * DB connection abstract declaration
 */
interface BF_DB_interface {
	/**
	 * Constructor
	 * @param	string	$db : Databse name to connect to (or, for sqlite, name of file)
	 * @param	string	$host [optional default null] : The DB server. It can also include a port number. e.g. "hostname:port" or a path to a local socket e.g. ":/path/to/socket" for the localhost. For sqlite DB, it will be the folder ID where db is.
	 * @param	string	$username [optional default null] : The username
	 * @param	string	$password [optional default null] : The password
	 */
	public function __construct($db, $host = null, $user = null, $password = null);
	
	/**
	 * Close connection
	 */
	public function close();
	
	/**
	 * Return number of results for a given result resource ID or statement
	 * If not supported by database, then return false
	 * @param	resource	$result [optional default null] : resource ID or statement, null for last
	 * @return	int 	Number of rows or false if not supported
	 */
	public function num_rows($result = null);
	/**
	 * Return number of rows that were affected by last query
	 * @param	resource	$result [optional default null] : resource ID or statement, null for last
	 * @return	int 	Number of rows
	 */
	public function affected_rows($result = null);
	/**
	 * Return row array at given offset for a given result resource ID or statement
	 * @param	int 	$offset : row offset (starting at 0)
	 * @param	resource	$result [optional default null] : resource ID or statement, null for last
	 * @return	array 	Associative array
	 */
	public function seek_row($offset, $result = null);
	/**
	 * Return next row array for a given result resource ID or statement
	 * @param	resource	$result [optional default null] : resource ID or statement, null for last
	 * @return	array 	Associative array
	 */
	public function fetch_array($result = null);
	
	/**
	 * Return the ID of the last inserted row
	 * @return	int 	Value of the autoincrement column of last inserted row
	 */
	public function last_insert_id();
	/**
	 * Perform a SQL query on db. Will throw exception on failure
	 * @param 	string	$query : A SQL select query
	 * @return	bool	true on success
	 */
	public function query ($query);
	
	/**
	 * Return an array of result from database
	 * @param 	string	$query : A SQL select query
	 * @return	array
	 * @warning	Condition values getten from a user input need to be quoted using Q for security issue
	 */	
	public function get_query ($query);
	
	/**
	 * Return first result from database that match condition
	 * @param 	string	$query : A SQL select query
	 * @return	array
	 * @warning	Condition values getten from a user input need to be quoted using Q for security issue
	 */
	public function get_first ($query);
		
	/**
	 * Get all values of a field (column) from a table
	 * @param 	string	$query : A SQL select query
	 * @param	string	$field [optional default NULL] : The field, to extract, by default the first field returned
	 * @return	array	list of all field values (may contain duplicates)
	 */
	public function get_field($query, $field = null);

	/**
	 * Count a number of results from query
	 * @param	mixed	$table : table alias. Can also be an array of tables. Each must be in the form 'tablealias as mytable'
	 * @param 	string	$condition [optional default NULL] : SQL query condition. If you need to refer to a field of a particular table, define table as 'tablealias as mytable' and use 'mytable.field' in the condition instead
	 * @param	string	$extra [optional default NULL] : to be added at the end of the query. For example : "GROUP BY field"
	 * @return	int		Number of results
	 */
	public function count ($table, $condition = NULL, $extra = NULL);
	
	/**
	 * Set some value in database where condition's true
	 * @param	string	$table : table alias.
	 * @param 	string	$condition [optional default NULL] : SQL query condition
	 * @param	array	$fields : Associative array of all fields to set in DB. You may use names in the form : "tablealias.field as field", table name's will then be converted to real tables' names as defined in config file.
	 * @param	bool	$force [optional default false] : If force is true, will try to insert entry if it does not exists
	 * @return	bool	true on success, false otherwise
	 * @warning	Condition values taken from a user input need to be quoted using Q for security issue
	 */
	public function set ($table, $condition, $fields, $force = false);
	
	/**
	 * Add a new entry in database
	 * @param	string	$table : a table alias.
	 * @param	array	$fields : Associative array of all fields to set in DB.
	 * @return	int 	ID of new entry on success, false otherwise
	 */
	public function add ($table, $fields);
	
	/**
	 * Add multiple entry in database
	 * @param	string	$table : a table alias
	 * @param	array	$columns : List of all columns to fill
	 * @param	array	$entries : Two dimmension array : List of array of values to add in each columns. Must respect order of $columns.
	 * @return	int 	ID of last entry made on success, false otherwise
	 */
	public function add_multiple ($table, $columns, $entries);
	
	/**
	 * Delete entries from DB where condition is true
	 * @param	string	$table : table alias
	 * @param 	string	$condition : SQL query condition
	 * @return	bool	true on success, false otherwise
	 * @warning	Condition values taken from a user input need to be quoted using Q for security issue
	 */
	public function del ($table, $condition);
	
	/**
	 * Test if server supports this db type
	 * @return	bool
	 */
	public static function supported();
	
	/**
	 * Quote a string to be inserted in a query (for a condition or a set)
	 * @param	string	$val : value to be quoted
	 * @return	string quoted, including outer quotes
	*/
	public function Q($val = "");
	
}


?>
