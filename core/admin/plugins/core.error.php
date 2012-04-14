<?php

/**
 * Configuration file for error handling
 */


config::reg_section("error", array("title" => "Error handling"));

config::reg_subsection("error", "mode", array("title" => "Error handling"));

config::reg_subsection("error", "log", array("title" => "Error log"));


//// mode


config::reg_field("error", "mode", "error_reporting", array(
	"title" => "PHP error reporting level",
	"type" => "select",
	"default" => E_ALL | E_STRICT,
	"options" => array(
		0 => 'None',
		E_ERROR | E_WARNING | E_PARSE => 'E_ERROR | E_WARNING | E_PARSE',
		E_ERROR | E_WARNING | E_PARSE | E_NOTICE => 'E_ERROR | E_WARNING | E_PARSE | E_NOTICE',
		E_ALL ^ E_NOTICE ^ E_STRICT => 'E_ALL ^ E_NOTICE ^ E_STRICT',
		E_ALL ^ E_STRICT => 'E_ALL ^ E_STRICT',
		E_ALL | E_STRICT => 'E_ALL | E_STRICT'
	),
	"onsave" => "config_error_save",
	"desc" => "PHP error reporting level refert to php doc on error_reporting() "
	)
);
config::reg_field("error", "mode", "error_debug", array(
	"title" => "Debug mode",
	"type" => "radio",
	"options" => array(0 => "Hide errors (for production)", 1 => "Display debug informations on error"),
	"default" => true,
	"cast" => "bool",
	"desc" => ""
	)
);

function config_error_save ($id, $input, &$conf) {
	if ($input == 0) {
		$conf[$id] = "0";
		return;
	}
	foreach (config::$fields["error"]["mode"][$id]["options"] as $value => $text) {
		if ($value == $input) {
			$conf[$id] = $text;
			return;
		}
	}
}


//// log 
config::reg_field("error", "log", "error_log", array(
	"title" => "Log errors",
	"type" => "checkbox",
	"label" => "Enabled",
	"cast" => "bool",
	"default" => true,
	"desc" => "Log errors in errorlog.txt which will be created in the Data directory"
	)
);





