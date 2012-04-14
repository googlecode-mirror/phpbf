<?php

/**
 * Configuration file for time
 */


config::reg_section("general", array("title" => "General"));

config::reg_subsection("general", "general", array("title" => "General settings"));


//// general

config::reg_field("general", "general", "project_id", array(
	"title" => "Project ID",
	"type" => "text",
	"default" => "",
	"desc" => "If more than one project runs the Framework on a single webserver, you may need to set a unique project ID to prevent sessions and coockies form being mixed up"
	)
);
config::reg_field("general", "general", "general_file_to_load", array(
	"title" => "File to load",
	"type" => "text",
	"default" => "",
	"advanced" => true,
	"desc" => "If project requires a file to be loaded every time with some custom contants and function definition (eg. myproject.php), enter the name here, or leave blank. File must be placed in the same directory as framework.php and will be loaded right after the framework"
	)
);
config::reg_field("general", "general", "encoding", array(
	"title" => "Encoding",
	"type" => "select",
	"default" => 'UTF-8',
	"options" => array(
		'UTF-8' => "UTF-8",
		'ISO-8859-1' => 'ISO-8859-1',
		'ISO-8859-15' => 'ISO-8859-15',
		'cp866' => 'cp866',
		'cp1251' => 'cp1251',
		'cp1252' => 'cp1252',
		'KOI8-R' => 'KOI8-R',
		'BIG5' => 'BIG5',
		'GB2312' => 'GB2312',
		'BIG5-HKSCS' => 'BIG5-HKSCS',
		'Shift_JIS' => 'Shift_JIS',
		'EUC-JP' => 'EUC-JP'
	),
	"desc" => "Char encoding site wide. UTF-8 is default and is recommended. <br/>Note: All translations files are by default encoded in UTF-8",
	"advanced" => true
	)
);
