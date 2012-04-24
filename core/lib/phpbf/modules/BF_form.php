<?php

/*-------------------------------------------------------*\
|  PHP BasicFramework - Ready to use PHP site framework   |
|  License: LGPL, see LICENSE                             |
\*-------------------------------------------------------*/

/**
* Form module
* @file form.php
* @package PhpBF
* @subpackage form
* @version 0.7
* @author L. Minghetti
* @date Started on the 2007-10-21
*/


/**
 * ON THE 2008-01-09 A MAJOR CHANGE WAS MADE SO THAT IT DOES NOT RELY ANYMORE ON THE PAGE MODULE
 * ON THE 2008-05-29 THE FORM PACKAGE WAS PARTIALY REWRITED AND MAJOR CHANGES WERE MADE
 */

// Security
if (!defined('C_SECURITY')) exit;


/**
 * Return a form object by reference for the given form id
 * @param	mixed	$id [optional default null] : Name of the file containing form data (without the .frm extension). If null, will just return a new form object, with no initial data loaded. If a form object is passed, then form object is just returned
 * @return	object	Form object
 */
function gform($id = null) {
	if (!$id) return new BF_form();
	if ($id instanceof BF_form) return $id;
	/// Contains all loaded forms
	static $forms = array();
	// load form if not loaded yet
	if (!isset($forms[$id])) $forms[$id] = new BF_form($id);
	//return form (object are always returned by reference
	return $forms[$id];
}

/**
 * Form class to deal with form creation and inputs
 */
class BF_form {
	
	const email_regex = '.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,}|[0-9]{1,3})(\]?)';
	
	/**
	 * @static	array	properties : List of all reserved property names
	 */
	public static $properties = Array('auto_resize','invalid_classname','options_suggest','suggest_callback','invalid_callback','valid_callback','no_trim','editable','min_length','max_length','not_empty','reg_match','is_email','is_multiple_emails','allowed_chars','banned_chars','custom','custom_callback','in_array','not_in_array','is_int','is_numeric','min_value','max_value','is_same_as');
	/**
	 * @var 	array	$fields : List of all fields declared for this form
	 */
	public $fields = Array();
	
	/**
	 * @var 	string	$method : Form method
	 */
	public $method = 'post';
	
	/**
	 * @var 	string	$enctype : data encoding for transmission (alias 'file' can be used for 'application/x-www-form-urlencoded')
	 */
	public $enctype = '';
	/**
	 * @var	string	$error : Contains last error message
	 */
	private $error = '';

	/**
	 * Constructor
	 * @param	string	$id [optional default null] : Name of the file containing form data (without the .frm extension). If null, no data will be loaded initialy
	 * @param	string	$method [optional default POST] : form method : either GET or POST
	 */
	public function __construct($id = null, $method = 'post') {
		$this->method = strtolower($method) == 'post'? 'post':'get';
		
		if ($id == null) $this->fields = Array();
		else {
			$source = BF::gr($id.".frm", 'forms');
			$compiled = BF::gr($id.(BF::gc('locale_enabled')? '.'.BF::gl()->locale : '').'.php', 'compiled_forms');
			
			if (!$source->exists()) throw new exception('No XML form data found for form id : '.$id.', make sure '.$id.'.frm is placed in the forms directory');
			// check if compiled exists and is not outdated
			if (!$compiled->exists() || filemtime($compiled->path()) < filemtime($source->path()))
			{
				// COMPILE FORM FILE
				// pase data xml file
				$fields = Array();	// output to be saved in the php file
				// open file and translate
				$source = file_get_contents($source->path());
				$xmlDoc = new DOMDocument();
				$xmlDoc->loadXML(BF::gc('locale_enabled')? BF::gl()->tl($source) : $source);
				
				// search for FIELD entries
				foreach ($xmlDoc->documentElement->childNodes as $field) {
					if ($field->nodeName == 'field' && $field->hasAttribute('name')) {
						$name = $field->getAttribute('name');
						$fields[$name] = Array();
						// add all attributes
						foreach ($field->attributes as $attr) $fields[$name][$attr->nodeName] = $attr->nodeValue;
						// add all properties and options
						foreach ($field->childNodes as $item) {
							// add all properties
							if ($item instanceof DOMElement && $item->tagName == 'property' && $item->hasAttribute('name')) {
								$fields[$name]['properties'][$item->getAttribute('name')] = Array();
								$property =& $fields[$name]['properties'][$item->getAttribute('name')];
								
								if ($item->hasAttribute('delay')) $property['delay'] = (int)$item->getAttribute('delay');
								// if value attribute is set
								if ($item->hasAttribute('value')) {
									$property['value'] = $item->getAttribute('value');
								// else get value could be an array of nested option/item tags
								} else {
									// each option/item
									foreach ($item->childNodes as $property_item) {
										// add an option value
										if ($property_item instanceof DOMElement && $property_item->tagName == 'option') {
											// add some options to this property
											$option = array('text' => '');
											foreach ($item->childNodes as $child) $option['text'] .= $xmlDoc->saveXML($child);
											// add all option's attributes
											foreach ($property_item->attributes as $attr) $option[$attr->nodeName] = $attr->nodeValue;
											$property['value'][] = $option;
										// add an item value
										} elseif ($property_item instanceof DOMElement && $property_item->tagName == 'item' && $property_item->hasAttribute('value')) {
											$property['value'][] = $property_item->getAttribute('value');
										}
									}
									// in case no option/item were found
									//if (!isset($fields[$name]['properties'][$item->getAttribute('name')]['value'])) $fields[$name]['properties'][$item->getAttribute('name')] = ;
								}
								// text to display in case value is invalid
								if ($item->hasAttribute('invalid_message')) $property['invalid_message'] = $item->getAttribute('invalid_message');
								elseif (BF::gc('locale_enabled') && BF::gl()->block_exists('form.error_'.$item->getAttribute('name'))) {
									// see if there are any default message to display
									$property['invalid_message'] = BF::gl()->tl_tag(
										'form.error_'.$item->getAttribute('name')
										.'|'.(isset($property['value'])? $property['value'] : '')
										.'|'.ucfirst(isset($fields[$name]['title'])? $fields[$name]['title'] : (isset($fields[$name]['alt'])? $fields[$name]['alt'] : $fields[$name]['name']))
									);
								}
							// add all options
							} elseif ($item->nodeName == 'option') {
								// add a field option
								$option = array('text' => '');
								foreach ($item->childNodes as $child) $option['text'] .= $xmlDoc->saveXML($child);
								// add all option's attributes
								foreach ($item->attributes as $attr) $option[$attr->nodeName] = $attr->nodeValue;
								$fields[$name]['options'][] = $option;
							}
						}
					}
				}
				// save php to file
				if (file_put_contents($compiled->path(), '<?php return '.var_export($fields,true).'; ?>') === false) throw new exception('Failed writing compiled form file, path : '.$compiled->path());
			}
			// load form fields
			$this->fields = $compiled->load();
		}
	}
	
	/**
	 * Get submited value of a field
	 * @param	string	$field : Name of the field
	 * @return	string	value of field as submited by user, or null if NA. If no_trim is on, or field is of type password, value will not be trimed
	 */
	public function gval($field) {
		if ($this->method == 'post') $value = isset($_POST[$field])? $_POST[$field] : null;
		else $value = isset($_GET[$field])? $_GET[$field] : null;
		if ($value === null) return $value;
		return $this->has_property($field, 'no_trim') || (isset($this->fields[$field]['type']) && $this->fields[$field]['type'] == 'password')? $value : trim($value);
	}
	
	/**
	 * Get field data by reference
	 * @param	string	$field : Name of the field
	 * @return	array	Returns field array by reference
	 */
	public function & get_field($field) {
		if (!isset($this->fields[$field])) $this->fields[$field] = Array();
		return $this->fields[$field];
	}
	
	/**
	 * Get options by reference of a field
	 * @param	string	$field : Name of the field
	 * @return	array	Returns array of options by reference
	 */
	public function & get_options($field) {
		$field =& $this->get_field($field);
		if (!isset($field['options'])) $field['options'] = Array();
		return $field['options'];
	}
	/**
	 * Check if field has a given option (value)
	 * @param	string	$field : Name of the field
	 * @param	string	$value : Value of option
	 * @return	bool	True if has option value, false otherwise
	 */
	public function has_option($field, $value) {
		$options = $this->get_options($field);
		foreach ($options as $option) {
			if (isset($option["value"]) && $option["value"] === $value) return true;
		}
		return false;
	}
	
	/**
	 * Get properties by reference of a field
	 * @param	string	$field : Name of the field
	 * @return	array	Returns array of properties by reference
	 */
	public function & get_properties($field) {
		$field =& $this->get_field($field);
		if (!isset($field['properties'])) $field['properties'] = Array();
		return $field['properties'];
	}
	
	/**
	 * Check if field has a given property
	 * @param	string	$field : Name of the field
	 * @param	string	$property : Name of the property
	 * @return	bool	True if has property, false otherwise
	 */
	public function has_property($field, $property) {
		$properties = $this->get_properties($field);
		return array_key_exists($property, $properties);
	}
	
	/**
	 * Get property for a field
	 * @param	string	$field : Name of the field
	 * @param	string	$property : Name of the property
	 * @return	mixed	Array of property data or false if not found (use '=== false' to make sure property was not found)
	 */
	public function get_property($field, $property) {
		$properties = $this->get_properties($field);
		return array_key_exists($property, $properties)? $properties[$property] : false;
	}

	/**
	 * Return JS code to print after a field's input/select/etc. tag, to be interpreted by the AFC script.
	 * @warning : Prototype.js and form.js need to be loaded previously to executing this code
	 * @param	string	$field : Name of the field to print JS
	 * @param	string	$id : ID of the input
	 * @return	string	JS code
	 */
	public function get_js($field, $id) {
		return '$("#'.$id.'").afc('.Q($this->get_properties($field), Q_JSON).');';
	}
	
	/**
	 * Return user friendly title of a field, based on the available attributes. Will first look for title, then alt, and return name if none found
	 * @param	string	$field : Name of the field
	 * @return	string	User friendly name for input
	 */
	function get_title($field) {
	 	$field = $this->get_field($field);
	 	return isset($field['title'])? $field['title'] : (isset($field['alt'])? $field['alt'] : (isset($field['name'])? $field['name']:""));
	}
	
	/**
	 * Check some or all fields associated to the form if the match their respective conditions as set with add_condition.
	 * This is to be called to make sure user input is valid. If the form was printed with the {input} smarty functions, then JS must have already checked all conditions
	 * However, you should always run this server test, in case JS was desactivated or malicious users trying to bypass js check
	 * @param	string	[$field1, [$field2, ...]] : List of all fields names to check, if none, then all are checked
	 * @return	bool	true if valid, false otherwise. see error property to get more info on error
	 */
	public function check() {
		$list = func_get_args();
		foreach(array_keys($this->fields) as $name) {
			if (count($list) == 0 || in_array($name, $list)) {
				if (!$this->check_field_value($name, $this->gval($name))) return false;
			}
		}
		return true;
	}
	
	/**
	 * Function to check a field value match all conditions set. (Use this function if you need to check for a given value instead of the one sent by form, otherwise you should call check() or check(input) instead)
	 * @warning : custom and custom_callback cannot be checked as they are JS only conditions. You should manualy check that these conditions are matched
	 * @param	string	$field : Name of the field to check
	 * @param	string	$value : Value of the field to check
	 * @return	bool	true on all match, false if some don't match
	 */
	 public function check_field_value($field, $value) {
	 	if (!is_array($this->fields[$field])) throw new exception('Undefined field name : '.$field);
	 	
	 	// check each conditions
	 	foreach ($this->get_properties($field) as $p_name => $p_data) {
	 		$condition_value = isset($p_data['value'])? $p_data['value'] : '';
	 		switch ($p_name) {
	 			case 'min_length' : $valid  = strlen($value) >= $condition_value; break;
	 			case 'max_length' : $valid = strlen($value) <= $condition_value; break;
	 			case 'not_empty' : $valid = strlen($value) != 0; break;
	 			case 'reg_match' : $valid = preg_match($condition_value, $value); break;
	 			case 'is_email' : $valid = preg_match('/^'.BF_form::email_regex.'$/', $value); break;
	 			case 'is_multiple_emails' : $valid = preg_match('/^('.BF_form::email_regex.')(\s*,\s*('.BF_form::email_regex.')?)*$/', $value); break;
	 			case 'allowed_chars' : 
	 				$valid = false;
	 				for ($i = strlen($value)-1; $i >= 0; $i--) {
	 					if (strpos($condition_value, $value[$i]) === false) break 2;
	 				}
	 				$valid = true;
	 				break;
	 			case 'banned_chars' : 
	 				$valid = false;
	 				for ($i = strlen($condition_value)-1; $i >= 0; $i--) {
	 					if (strpos($value, $condition_value[$i]) !== false) break 2;
	 				}
	 				$valid = true;
	 				break;
	 			case 'in_array' :
	 				if (!is_array($condition_value)) $condition_value[] = $condition_value;
	 				$valid = in_array($value, $condition_value);
	 				break;
	 			case 'not_in_array' :
	 				if (!is_array($condition_value)) $condition_value[] = $condition_value;
	 				$valid = !in_array($value, $condition_value);
	 				break;
	 			case 'is_int' :	$valid = is_numeric($value) && intval($value) == $value;break;
	 			case 'is_numeric' : $valid = is_numeric($value);break;
	 			case 'min_value' : 
	 				if ($this->has_property($field, 'is_int')) $valid = (int)$value >= (int)$condition_value;
	 				else $valid = $value >= $condition_value;
	 				break;
	 			case 'max_value' : 
	 				if ($this->has_property($field, 'is_int')) $valid = (int)$value <= (int)$condition_value;
	 				else $valid = $value <= $condition_value;
	 				break;
	 			case 'is_same_as' : $valid = $value == $this->gval($condition_value); break;
	 			case 'is_in_options' : $valid = $this->has_option($field, $value); break;
	 			default : $valid = true; break;
	 		}
	 		// if this condition is not valid
			if ($valid == false) {
				$this->error = Array('field'=>$field, 'property'=>$p_name);
				return false;
			}
	 	}
	 	// return true if all conditions matched
	 	return true;
	 }
	 
	 
	 /**
	  * Show last error message by throwing a BF_invalid_form
	  * Note : shows only one error message for last invalid field
	  * @return	bool	false if no error, throw exception otherwise
	  */
	 public function show_error() {
	 	if (!$this->error) return false;
	 	$condition = $this->get_property($this->error['field'], $this->error['property']);
	 	if (!isset($condition['invalid_message'])) $condition['invalid_message'] = $message = BF::gl()->block_exists('form.error_'.$this->error['property'])? 'form.error_'.$this->error['property']:'form.error'.'|'.(isset($condition['value'])? $condition['value']:"").'|'.$this->get_title($this->error['field']);
	 	throw new BF_invalid_form($condition['invalid_message']);
	 }
	 
	 /**
	  * Returns description validation error
	  */
	 public function get_last_error() {
	 	return $this->error;
	 }

}
?>
