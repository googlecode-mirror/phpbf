<?php
/*
 * Smarty plugin : input function
 * -------------------------------------------------------------
 * File:     function.input.php
 * Type:     function
 * Name:     input
 * Usage:    {input src='...' alt='' hover=''}
 * param:    string	href or action [optional] : file to point, see doc of BF::gr function for detail 
 * Purpose   print an input tag with validation check
 * Params:		type : input type, can be : text, hidden, textarea, password, radio, checkbox, file, button, submit, message, select, iselect, radioset, image_verification
 * 			[id] : input unique id. If not set, will take name as id
 * 			[name] : If not set, will take ID as name. Single radio element should use the same name for the whole set
 * 			[label] : label for radio, checkbox and button (creates a label tag after input tag)
 * 			[form_data] : Override form data for this input (can be used if input is not nested in a form)
 * 			[properties] (array) : array that contains all parameters/conditions instead of giving them separately
 * 			[options] (array) : array of options (for select, iselect, radioset, etc.)
 * 
 * 			> Properties
 * 
 * 				Behaviors :
 * 			[auto_resize] : Automaticly resize textarea
 * 			[options_suggest] (array) : for text input only, suggest value based on list
 * 			[suggest_callback] : a js function that returns a list of suggestions based on user input
 * 			[invalid_callback] : When an input conditions are not fulfilled, a JS user function can be called
 * 			[valid_callback] : When all input's conditions are filled, a JS user function can be called
 * 			[invalid_classname] : Class name to set if value is invalid
 * 			[no_trim] : Do not trim value before submit and before running checks (values are trimed by default for all input types except password)
 * 			[editable] : For iselect only : Allows user to edit value by typing instead
 * 
 * 				Conditions:
 * 			[min_lenth] : minimal length of the text field
 * 			[max_length] : maximal length of the text field
 * 			[not_empty] : field can be empty (Note: this will only be checked after user has started typing something) Can be used for select too
 * 			[reg_match] : text field must match a reg
 * 			[is_email] : text field must be valid email
 * 			[is_multiple_emails] : text field must be a list of valid comma-separated emails
 * 			[allowed_chars] : list of chars that user can type
 * 			[banned_chars] : list of chars that user cannot use
 * 			[custom] : A custom javascript function. Warning: check will not be performed on server side. Should be tested manualy
 * 			[custom_calback] : A custom asynchronous javascript function. Warning: check will not be performed on server side. Should be tested manualy
 * 			[in_array] : Check that value is in given array of possible values
 * 			[not_in_array] : Check that value is not in given array of values
 * 			[is_int] : Check that value is an integer
 * 			[is_numeric] : Check that value is a numeric
 * 			[is_same_as] : Check that value is same as another field with given name
 * 			[min_value] : Value must be greater or equal to...
 * 			[max_value] : Value must be inferior or equal to ...
 * 			[is_in_options] : Value must be in given options (security check on server side only for radio and select fields)
 *
 * 			any other attributes will be appended to the input tag
 * @warning  Requires jQuery and jQuery UI
 * -------------------------------------------------------------
 */

function smarty_function_input($p, Smarty_Internal_Template $template)
{
	$smarty = $template->smarty;
	BF::load_module('BF_form');
	
	// Determine input ID and field name
	if (!isset($p['id']) || $p['id'] == '') {
		static $counter = 0;
		$id = $p['id'] = 'input_unnamed_'.++$counter;
	} else $id = $p['id'];
	if (!isset($p['name']) || $p['name'] == '') $name = $p['name'] = $p['id'];
	else $name = $p['name'];
	
	// Load form data from <form> tag, or from a given form_data
	if (isset($p['form_data'])) {
		$form = gform($p['form_data']);
	} else {
		for ($i = count($smarty->_tag_stack)-1; $i>=0; $i--) {
			if ($smarty->_tag_stack[$i][0] == 'form') {
				$form = $smarty->_tag_stack[$i][1]['data'] = gform(isset($smarty->_tag_stack[$i][1]['data'])? $smarty->_tag_stack[$i][1]['data'] : null);
				break;
			}
		}
	}
	if (!isset($form)) $form = gform();	// if no forms were found, then juste create a new form object

	// properties set as parmeters and not in the properties array should be appended
	foreach($p as $param => $value) {
		if (in_array($param, BF_form::$properties)) {
			$p['properties'][$param]['value'] = $value;
			// see if there are any default invalid message to display
			if (BF::gc('locale_enabled') && BF::gl()->block_exists('form.error_'.$param)) {
				$p['properties'][$param]['invalid_message'] = BF::gl()->tl_tag(
					'form.error_'.$param
					.'|'.$value
					.'|'.ucfirst(isset($p['title'])? $p['title'] : ($form->get_title($name)? $form->get_title($name) : (isset($p['alt'])? $p['alt'] : $name)))
				);
			}
			
			
			unset($p[$param]);
		}
	}
	// get data from field and append passed properties and options
	$field =& $form->get_field($name);
	// mix prameters and field data, parameters override field data
	if (isset($p['properties']) && is_array($p['properties'])) $field['properties'] = array_merge(isset($field['properties']) && is_array($field['properties'])? $field['properties']:array(), $p['properties']);
	if (isset($p['options']) && is_array($p['options'])) $field['options'] = array_merge(isset($field['options']) && is_array($field['options'])? $field['options']:array(), $p['options']);
	$p = array_merge($field, $p);
	$p['properties'] =& $form->get_properties($name);
	$p['options'] =& $form->get_options($name);
	
	// get input type
	$type = isset($p['type'])? $p['type'] : 'text';
	
	// if file input, set form enctype to multipart, if not already set
	if ($type == 'file' && !$form->enctype) $form->enctype = 'file';
	
	// if using max length, then add the html MAXLENGTH attribut too
	if ($form->get_property($name, 'max_length') > 0) {
		$maxlength = $form->get_property($name, 'max_length');
		$p['maxlength'] = $maxlength['value'];
	}
	
	// see if input is disabled or checked
	if (array_key_exists('disabled', $p)) {
		if ($p['disabled']) $p['disabled'] = 'disabled';
		else unset($p['disabled']);
	}
	if (array_key_exists('readonly', $p)) {
		if ($p['readonly']) $p['readonly'] = 'readonly';
		else unset($p['readonly']);
	}
	if (array_key_exists('checked', $p)) {
		if ($p['checked']) $p['checked'] = 'checked';
		else unset($p['checked']);
	}
	if (array_key_exists('multiple', $p)) {
		if ($p['multiple']) $p['multiple'] = 'multiple';
		else unset($p['multiple']);
	}
	
	
	// init the output string
	$html = "";
	
	// compute options list for iselect/select/radioset
	if ($type == 'select' || $type == 'iselect' || $type == 'radioset') {
		$options_html = '';
		$selected_option = false;
		
		if ($type == 'iselect') $options_html .= "<div".$smarty->attr('id', $id.'_iselect_div').$smarty->attr('class', 'input_iselect_list').">";
		
		if (isset($p['options']) && is_array($p['options'])) {
			foreach ($p['options'] as $option) {
				if (!is_array($option)) $option = array('value'=>$option);
				if (!isset($option['value'])) $option['value'] = '';
				if (!isset($option['text'])) $option['text'] = isset($option['label'])? $option['label'] : $option['value'];
				if ((isset($option['selected']) && $option['selected']) || (isset($option['checked']) && $option['checked']) || (isset($p['value']) && (string)$p['value'] === (string)$option['value']) && !$selected_option) {
					if ($type == 'radioset') $option['checked'] = 'checked';
					else $option['selected'] = 'selected';
					$p['value'] = $option['value'];
					if ($type != 'select' || !isset($p['multiple'])) $selected_option =& $option; // save selected option, so that we don't select followings options that have the same value
				} else unset($option['selected'], $option['checked']);
				
				if (isset($option['disabled']) && $option['disabled']) $option['disabled'] = 'disabled';
				else unset($option['disabled']);
				
				if ($type == 'iselect') $options_html .= '<a';
				else if ($type == 'select') $options_html .= '<option';
				else if ($type == 'radioset') {
					$option['class'] = 'input input_radio'.(isset($option['class'])? ' '.$option['class']:'');
					$options_html .= '<label><input type="radio"'.$smarty->attr('name', $name).(isset($p["onchange"])? $smarty->attr('onchange', $p["onchange"]):"").(isset($p["onclick"])? $smarty->attr('onclick', $p["onclick"]):"");
				}
				
				foreach ($option as $key => $val) {
					if ($key != 'text' && $key != 'label') $options_html .= $smarty->attr($key, $val);
				}
				
				if ($type == 'iselect') $options_html .= '>'.$option['text'].'</a>';
				elseif ($type == 'select') $options_html .= '>'.$option['text'].'</option>';
				elseif ($type == 'radioset') $options_html .= '/>'.$option['text'].'</label>';
			}
			
		}
		
		if ($type == 'iselect' || $type == 'radioset') unset($p["onchange"], $p["onclick"]);
		if ($type == 'iselect') {
			// if type is iselect, print options now and change type to normal text or hidden
			$html .= $options_html.'</div>';
			// by default, an iselect should be an hidden field, but it may also be a text field
			$type = $form->has_property($name, 'editable')? 'text':'hidden';
			// set the iselect property
			$p['properties']['iselect'] = array('value' => $id.'_iselect_div');
		}
	}
	
	// append common class names
	$p['class'] = 'input input_'.$type.(isset($p['class'])? ' '.$p['class']:'');
	
	// open html tag
	if ($type == 'textarea' || $type == 'button' || $type == 'select') $html .= "<".$type;
	elseif ($type == 'radioset') $html .= "<div";
	else $html .= "<input".$smarty->attr('type', $type);

	// append all other params
	$reserved = array('properties','options','label','type','form_data');
	if ($type == 'textarea') $reserved[] = 'value';
	//if ($type == 'checkbox' && isset($p['value']) && !isset($p['checked'])) {
	//	if ($p['value']) $p['checked'] = 'checked';
	//}
	foreach ($p as $key => $val) {
		if ($key != '' && !in_array($key, $reserved)) {
			$html .= $smarty->attr($key, $val);
		}
	}
	
	// close tag
	if ($type == 'textarea') $html .= ">".htmlspecialchars(isset($p['value'])? $p['value'] : '', ENT_COMPAT, BF::$encoding)."</textarea>";
	elseif ($type == 'button') $html .= ">".htmlspecialchars(isset($p['label'])? $p['label'] : (isset($p['value'])? $p['value'] : ''), ENT_COMPAT, BF::$encoding)."</button>";
	elseif ($type == 'select') $html .= ">".$options_html."</select>";
	elseif ($type == 'radioset') $html .= ">".$options_html."</div>";
	else $html .= " />";
	
	// print label for radio, checkbox, etc...
	if (isset($p['label']) && $p['label'] != '') $html .= '<label'.$smarty->attr('for', $id).'>'.$p['label'].'</label>';

	// if there are at least one property
	if (count($p['properties'])) {
		// load js file if it is the first time a field requires such options
		/*static $js_loaded;
		if (!$js_loaded) {
			$html .= $smarty->tpl_js(Array(), 'if (window.Prototype == undefined) document.write('.str_replace('/', '\\/', Q($smarty->tpl_js(Array('src' => "lib/prototype.js"), '', $smarty))).');', $smarty);
			$html .= $smarty->tpl_js(Array(), 'if (window.advancedFormControl == undefined) document.write('.str_replace('/', '\\/', Q($smarty->tpl_js(Array('src' => "form.js"), '', $smarty))).');', $smarty);
			// Note : Of script is loaded that way, then it might not be laoded yet when next line is executed
			//$html .= $smarty->tpl_js(Array(), 'if (window.advancedFormControl == undefined) document.getElementsByTagName("head")[0].appendChild(new Element("script", {type: "text/javascript", src: "'.gf('js')->web_path.'form.js"}));', $smarty);
			$js_loaded = true;
		}*/
		
		BF::gr("/tags/block.js.php")->load_once();
		$load_js = smarty_block_js(array('src' => "lib/form.js"), "", $template);
		$html .= smarty_block_js(array(), 'if (afcUtils == undefined) document.write('.str_replace('/', '\\/', Q($load_js)).');', $template);
		$html .= smarty_block_js(array(), $form->get_js($name, $id), $template);
	}
	
	return $html;
}

?>
