/*-------------------------------------------------------*\
|  PHP BasicFramework - Ready to use PHP site framework   |
|                                                         |
|  Set of files to use as a common site structure.        |
|  Making database access, templates and design easier,   |
|  based upon the smarty template engine.                 |
|                                                         |
|  Copyright (C) 2004 - 2007  Loic Minghetti              |
|                                                         |
|  License: LGPL, see LICENSE                             |
|  Version : 0.2                                          |
\*-------------------------------------------------------*/


/**
 * This is an extension of Prototype to add check functionality to form inputs
 * @file form.js
 * @package PhpBF
 * @subpackage form
 * @version 0.7
 * @author Loic Minghetti
*/


/* Usage :

// Init and register a set of properties to be checked
$('#element').afc( {prop1: {value: value1}, ...} );

// Get array of properties (editable)
properties = $('#element').afc('properties');
properties.minvalue = "50";

// check if field is valid
if ( !$('#element').afc('check') ) {
	// invalid
}

// remove all afc events and data
$('#element').afc('destroy');


*/

(function( $ ){

	var methods = {
		init : function( newProperties ) {
		
			return this.each(function(){
				var $this = $(this);
				var data = $this.data('afc');
				
				// If the plugin hasn't been initialized yet
				if ( !data ) {
					$this.data('afc', {});
					data = $this.data('afc');
					data.properties = {};
					
					$this.closest("form").afc('initForm');
				}
				
				// add new properties
				$.each(newProperties, function (name, params) {
					afcUtils.addProperty($this, name, params);
				});
				
			});
		},
		properties : function( ) {
			return this.data('afc').properties || {};
		},
		check : function(onSubmit) {
			var valid = true;
			this.each(function(){
				var $this = $(this);
				
				if ($this[0].nodeName.toLowerCase() == 'form') {
					valid = $this.find("input, textarea, select, button").afc('check', onSubmit);
					if (!valid) {
						alert($this.data('afc').message);
					}
				} else {
					var data = $this.data('afc');
					if (!data) return true;
					var prop = data.properties || {};
				
					// do not perform check on disabled form fields
					if ($this.disabled) return true;
					if ($(this).parents(":hidden").length > 0) return true;
					// trim white spaces on submit
					if (onSubmit) $this.value = afcUtils.trim($this);
					data.valid = true;
					$.each(prop, function (name) {
						// check each property
						// if on submit, force a recheck
						if (onSubmit) afcUtils.checkProperty($this, name, 0); // with 0 delay
					
						if (!prop[name].valid) {
							data.valid = false;
							data.message = params.invalid_message || '';
							$this.closest("form").data('afc', {message: params.invalid_message || ''});
							return false;
						}
					});
				
					// take action
					$this.afc('update');
					if (!data.valid) {
						valid = false;
						return false;
					}
				}
			});
			return valid;
		},
		// refresh input style and call valid or invalid callback
		// validity may be forced with first argument
		update : function (valid) {
			
			return this.each(function(){
				var $this = $(this);
				var data = $this.data('afc');
				if (!data) return true;
				var prop = data.properties;
				
				if (valid === undefined) valid = data.valid;
				if (!valid) {
					if (prop.invalid_callback) eval(prop.invalid_callback.value)($this);
					$this.addClass(prop.invalid_classname? prop.invalid_classname:'input_invalid');
				} else {
					if (prop.valid_callback) eval(prop.valid_callback.value)($this);
					$this.removeClass(prop.valid_classname? prop.valid_classname:'input_invalid');
				}
			});
		},
		initForm : function () {
			return this.each(function(){
				var $this = $(this);
				
				if ($this.data('afc')) return true;
				$this.data('afc', {message: ""});
				
				var userOnSubmit = $this.onsubmit;
				$this.onsubmit = function(){};
				$this.on("submit.afc", {form: $this}, function (e) {
					if (!e.data.form.afc('check', true)) {
						e.stopImmediatePropagation();
						return false;
					}
					if (userOnSubmit && userOnSubmit(e) === false) {
						e.stopImmediatePropagation();
						return false;
					}
				});
				$this.on("reset", {form: $this}, function(e) {
					e.data.form.find("input, textarea, button, select").afc('check');
				});
			});
		},
		destroy : function( ) {

			return this.each(function() {
				var $this = $(this);
				//$this.unbind('.tooltip');
				$this.removeData('tooltip');
			});
		}
	};

	$.fn.afc = function( method ) {

		if ( methods[method] ) {
			return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.afc' );
		}    

	};

})( jQuery );




afcUtils = {
	
	addProperty: function (input, name, params) {
		
		if (!input[0].type && input[0].nodeName != "select" && input[0].nodeName != "textarea") return false;// if this is not an input field (but a select,textarea, etc...)
		
		if (name == 'iselect') {
			// todo
		} else {
			
			// clone property to input
			if (input.data('afc').properties[name]) {
				// update
				input.data('afc').properties[name] = $.extend(input.data('afc').properties[name], params);
				return;
			} else {
				input.data('afc').properties[name] = $.extend({}, params);
			}
			params = input.data('afc').properties[name];
			
			params.valid = true; 	// input may not be valid by default, but will display as a valid field until something is entered and checks are run
			
			// add the corresponding event listenner and set delay for certain conditions check
			// Note : some input do not support certain event, so they will be converted (eg. select will not take keydown, keydown will be converted to change)
			switch (name) {
				case 'is_int' :
				case 'is_numeric' : 
					if (input[0].nodeName == "textarea" || input[0].type == 'text' || input[0].type == 'password' || input[0].type == 'textarea') afcUtils.regEvent(input, 'blur');
					afcUtils.regEvent(input, 'keypress');
				case 'not_empty' : 
				case 'min_length' :
				case 'reg_match' :
				case 'is_email' :
				case 'is_multiple_emails' :
				case 'min_value' :
				case 'max_value' :
				case 'is_same_as' :
				case 'check_other' : 
				case 'in_array' :
				case 'not_in_array' :
				case 'custom' : 
					afcUtils.regEvent(input, 'keyup');
					break;
				case 'custom_callback' : 
					afcUtils.regEvent(input, 'keyup');
					if (params.delay == undefined) params.delay = 300;
					break;
				case 'allowed_chars' :
				case 'banned_chars' : 
					if (input[0].type == 'text' || input[0].type == 'password' || input[0].type == 'textarea') afcUtils.regEvent(input, 'blur');
				case 'max_length' :
					if (input[0].type == 'text' || input[0].type == 'password' || input[0].type == 'textarea') afcUtils.regEvent(input, 'keypress');
					break;
				// if property is not a condition, then it should always be valid, and check should always return true
				default : 
					params.valid = true;
					break;
			}
			
			// if no delay has been set
			if (params.delay == undefined) params.delay = 0;
		
	//		this.delayTimeout = false;
	//		if (this.name != 'custom_callback' && this.name != 'custom' && this.name != 'is_same_as' && this.name != 'check_other') this.check();	// init this.valid value
	//		else this.valid = true;
				
				
		}
	},
	
	regEvent: function(input, event) {

		
		if (input[0].nodeName == "select" || input[0].type == "checkbox") {
			input.on('change.afc', {input: input}, afcUtils.events[event]);
		} else {
			input.on(event+'.afc', {input: input}, afcUtils.events[event]);
		}
	},
	
	// all events that can be registered in order to check input data
	events : {
		keyup : function(e) {
			var input = e.data.input;
			var prop = input.data('afc').properties;
			
			if (prop['not_empty']) 		afcUtils.checkProperty(input, 'not_empty');
			if (prop['is_int']) 		afcUtils.checkProperty(input, 'is_int');
			if (prop['is_numeric'])		afcUtils.checkProperty(input, 'is_numeric');
			if (prop['min_value']) 		afcUtils.checkProperty(input, 'min_value');
			if (prop['max_value']) 		afcUtils.checkProperty(input, 'max_value');
			if (prop['is_same_as'])		afcUtils.checkProperty(input, 'is_same_as');
			if (prop['check_other']) 	afcUtils.checkProperty(input, 'check_other');
			if (prop['min_length'])		afcUtils.checkProperty(input, 'min_length');
			if (prop['reg_match'])		afcUtils.checkProperty(input, 'reg_match');
			if (prop['is_email']) 		afcUtils.checkProperty(input, 'is_email');
			if (prop['is_multiple_emails'])	afcUtils.checkProperty(input, 'is_multiple_emails');
			if (prop['custom']) 		afcUtils.checkProperty(input, 'custom');
			if (prop['custom_callback'])	afcUtils.checkProperty(input, 'custom_callback');
			
			// take action
			input.afc('check');
		},
		keypress : function(e) {
			var input = e.data.input;
			var prop = input.data('afc').properties;
			
			if (prop['is_int'] && !afcUtils.keyPermitted(e.which, "0123456789")) {e.stopImmediatePropagation(); return false;}
			if (prop['is_numeric'] && !afcUtils.keyPermitted(e.which, ".0123456789")) {e.stopImmediatePropagation(); return false;}
			if (prop['max_length'] && input[0].value.length >= prop.max_length.value && !afcUtils.keyPermitted(e.which, "a", "a")) {e.stopImmediatePropagation(); return false;}
			if (prop['allowed_chars'] && !afcUtils.keyPermitted(e.which, prop.allowed_chars.value)) {e.stopImmediatePropagation(); return false;}
			if (prop['banned_chars'] && !afcUtils.keyPermitted(e.which, false, prop.banned_chars.value)) {e.stopImmediatePropagation(); return false;}
			return true;
		},
		blur: function(e) {
			var input = e.data.input;
			var prop = input.data('afc').properties;
			
			input[0].value = afcUtils.trim(input);
			if (prop['is_int']) input[0].value = input[0].value.replace(/[^0-9]/gi,"");
			if (prop['is_numeric']) input[0].value = input[0].value.replace(/[^\.0-9]/gi,"");
			if (prop['allowed_chars']) input[0].value = eval('input[0].value.replace(/[^'+prop.allowed_chars.value.replace(/([\-.*+?^=!:${}()|[\]\/\\])/g, '\\$1')+']/gi,"")');
			if (prop['banned_chars']) input[0].value = eval('input[0].value.replace(/['+prop.banned_chars.value.replace(/([\-.*+?^=!:${}()|[\]\/\\])/g, '\\$1')+']/gi,"")');
			if (prop['max_length'] && input[0].value.length > prop.max_length.value) input[0].value = input[0].value.substring(0, prop.max_length.value);
			
			afcUtils.events.keyup(e);
		}
	},
	
	// See if a key (from keycode) is permited or not (used in keydown event)	
	keyPermitted: function (key, allowedChars, bannedChars) {
		// return true if given key code does not correspond to a character
		if (!key || key < 32 || (key >= 37 && key <= 40) || key == 127) return true;
		if (allowedChars && allowedChars.toUpperCase().indexOf(String.fromCharCode(key).toUpperCase()) == -1) return false;
		if (bannedChars && bannedChars.toUpperCase().indexOf(String.fromCharCode(key).toUpperCase()) != -1) return false;
		return true;
	},
	// trim string
	trim : function(input) {
		if (input.data('afc').properties.no_trim == undefined && input[0].type != 'password') {
			return input[0].value.replace(/^(\s)*/, '').replace(/(\s)*$/, '');
		} else {
			return input[0].value;
		}
	},
		
	// check if a property is met
	checkProperty : function(input, name, delay) {
		params = input.data('afc').properties[name];
		
		if (delay === undefined) delay = params.delay;
		if (delay != 0) {
			if (params.delayTimeout) clearTimeout(params.delayTimeout);
			params.delayTimeout = setTimeout(function() {
				afcUtils.checkProperty(input, name, 0);
				input.afc('check');
			}, delay);
			return;
		}
		switch (name) {
			case 'not_empty' : params.valid = afcUtils.trim(input).length != 0; break;
			case 'min_length' : params.valid = afcUtils.trim(input).length >= params.value; break;
			case 'custom' : 
				params.valid = eval(params.value)(this.input); 
				break;
			case 'custom_callback' : 
				//if (delay > 0) { // if not there is no time to check
					params.valid = true;
					eval(params.value)(input, function(valid) { 
						afcUtils.callback(input, valid);
					});
				//}
				break;
			case 'reg_match' : params.valid = eval('afcUtils.trim(input).match('+params.value+') != null'); break;
			case 'is_int' : params.valid = afcUtils.trim(input).match(/^[0-9]*$/) != null; break;
			case 'is_numeric' : params.valid = afcUtils.trim(input).match(/^[0-9]*(\.)?[0-9]*$/) != null; break;
			case 'is_email' : params.valid = afcUtils.trim(input).match(/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,}|[0-9]{1,3})(\]?)$/) != null; break;
			case 'is_multiple_emails' : params.valid = afcUtils.trim(input).match(/^(.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,}|[0-9]{1,3})(\]?))(\s*,\s*(.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,}|[0-9]{1,3})(\]?))?)*$/) != null; break;
			case 'min_value' : 
				if (input.data('afc').properties['is_int']) params.valid = (parseInt(input[0].value, 10) >= parseInt(params.value, 10)); 
				else params.valid = (input[0].value >= params.value);
				break;
			case 'max_value' : 				
				if (input.data('afc').properties['is_int']) params.valid = (parseInt(input[0].value, 10) <= parseInt(params.value, 10)); 
				else params.valid = (input[0].value <= params.value);
				break;
			case 'is_same_as' : 
				var other = $("#"+params.value);
				if (!other) params.valid = false;
				else {
					params.valid = input[0].value == other.val(); 
					if (!other.data('afc') || !other.data('afc').properties['check_other']) other.afc({check_other: {value:input}});
				}
				break;
			case 'check_other' : 
				if ($(params.value).data('afc').properties.is_same_as) {
					afcUtils.checkProperty($(params.value), 'is_same_as');
					// take action
					input.afc('check');
				}
				params.valid = true;
				break;
			case 'in_array' : // todo
			case 'not_in_array' :
			default : params.valid = true; break;
		}
	},

	// callback for the custom_callback condition
	callback : function(input, valid) {
		params = input.data('afc').properties.custom_calback;
		params.valid = valid;
		// take action
		input.afc('check');
	}
}







/*
	// Server query methods
	triggerServerSuggest : function () {
		if (this.timerServerSuggest) clearTimeout(this.timerServerOptions);
		var input = this;
		this.timerServerSuggest = setTimeout(function() {eval(input.conditions['server_suggest']+'(input.id, input.el.value);');}, 1000);
	}
*/



