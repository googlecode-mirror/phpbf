<?php

/**
 * Configuration file for sections and color schemes
 */


config::reg_section("sections", array("title" => "Sections and schemes"));

config::reg_subsection("sections", "schemes", array("title" => "Color schemes"));
config::reg_subsection("sections", "sections", array("title" => "Sections"));

//// schemes
config::reg_field("sections", "schemes", "colors", array(
	"title" => "Color schemes",
	"type" => "table",
	"columns" => array(
		"id" => array("title"=>"ID", "width"=>"10%"),
		0 => array("title"=>"0", "width"=>"10%", "maxlength"=>7),
		1 => array("title"=>"1", "width"=>"10%", "maxlength"=>7),
		2 => array("title"=>"2", "width"=>"10%", "maxlength"=>7),
		3 => array("title"=>"3", "width"=>"10%", "maxlength"=>7),
		4 => array("title"=>"4", "width"=>"10%", "maxlength"=>7),
		5 => array("title"=>"5", "width"=>"10%", "maxlength"=>7),
		6 => array("title"=>"6", "width"=>"10%", "maxlength"=>7),
		7 => array("title"=>"7", "width"=>"10%", "maxlength"=>7),
		8 => array("title"=>"8", "width"=>"10%", "maxlength"=>7),
	),
	"num" => "auto",
	"onsave" => "config_schemes_table_save",
	"onload" => "config_schemes_table_load",
	"default" => array(),
	"example" => array("id"=>"main", 0 => "#000000", 1 => "#7E0C0C", 2 => "#B82626", 3 => "#FB4444", 4 => "#FBCECE", 5 => "#FDECEC", 6 => "#FEFAFA", 7 => "#FFFFFF", 8 => "#FFFFFF"), 
	"desc" => "You can set up to 9 colors for each color scheme. They must be in the form : #XXXXXX.<br/>Colors defined in color scheme may be accessed in CSS and in templates. If using section, you may define more than one color scheme, and associate each color to a section. If not, first color will be used as default."
	)
);
function config_schemes_table_load ($field_id, $raw) {
	$output = array();
	if (is_array($raw)) {
		foreach ($raw as $id => $colors) {
			$colors["id"] = $id;
			$output[] = $colors;
		}
	}
	return $output;
}
function config_schemes_table_save ($field_id, $input, &$conf) {
	$output = array();
	for ($i=0; $i< count($input['id']); $i++) {
		$id = trim($_POST['colors']['id'][$i]);
		if ($id)
			$output[$id] = Array(
				preg_match('/^\#[0-9A-Fa-f]{6}$/', $input[0][$i])? strtoupper($input[0][$i]):'#FFFFFF', 
				preg_match('/^\#[0-9A-Fa-f]{6}$/', $input[1][$i])? strtoupper($input[1][$i]):'#FFFFFF', 
				preg_match('/^\#[0-9A-Fa-f]{6}$/', $input[2][$i])? strtoupper($input[2][$i]):'#FFFFFF', 
				preg_match('/^\#[0-9A-Fa-f]{6}$/', $input[3][$i])? strtoupper($input[3][$i]):'#FFFFFF', 
				preg_match('/^\#[0-9A-Fa-f]{6}$/', $input[4][$i])? strtoupper($input[4][$i]):'#FFFFFF', 
				preg_match('/^\#[0-9A-Fa-f]{6}$/', $input[5][$i])? strtoupper($input[5][$i]):'#FFFFFF', 
				preg_match('/^\#[0-9A-Fa-f]{6}$/', $input[6][$i])? strtoupper($input[6][$i]):'#FFFFFF', 
				preg_match('/^\#[0-9A-Fa-f]{6}$/', $input[7][$i])? strtoupper($input[7][$i]):'#FFFFFF', 
				preg_match('/^\#[0-9A-Fa-f]{6}$/', $input[8][$i])? strtoupper($input[8][$i]):'#FFFFFF'
			);
	}
	$conf[$field_id] = $output;
}


//// sections
config::reg_field("sections", "sections", "use_sections", array(
	"title" => "Use sections",
	"type" => "checkbox",
	"label" => "Divide site into sections",
	"cast" => "bool",
	"default" => false,
	"desc" => "Each section can have a different color scheme and/or access restriction"
	)
);
config::reg_field("sections", "sections", "page_section_div", array(
	"title" => "Template sections mode",
	"type" => "select",
	"default" => '.',
	"options" => array(
		'.' => 'Use / : Sort by folder',
		'/' => 'Use . : Sort by prefix'
	),
	"desc" => "Change how pages are sorted into sections ",
	"advanced" => true
	)
);
config::reg_field("sections", "sections", "sec", array(
	"title" => "Sections",
	"type" => "table",
	"columns" => array(
		"id" => array("title"=>"ID", "width"=>"15%"),
		"scheme" => array("title"=>"Color scheme", "width"=>"25%"),
		"title" => array("title"=>"Title", "width"=>"35%"),
		"access" => array("title"=>"Permissions")
	),
	"num" => "auto",
	"onsave" => "config_sections_table_save",
	"onload" => "config_sections_table_load",
	"default" => array(),
	"example" => array("id"=>"admin", "scheme" => "main", "title" => "Admin section", "access" => "g:admin"), 
	"desc" => "Only ID is necessary, other fields can be left blank"
	)
);
function config_sections_table_load ($field_id, $raw) {
	$output = array();
	if (is_array($raw)) {
		foreach ($raw as $id => $data) {
			$output[] = array("id" => $id, "scheme" => $data[0], "title" => $data[1], "access" => $data[2]);
		}
	}
	return $output;
}
function config_sections_table_save ($field_id, $input, &$conf) {
	$output = array();
	for ($i=0; $i< count($input['id']); $i++) {
		$id = trim($input['id'][$i]);
		if ($id) $output[$id] = array(trim($input['scheme'][$i]), trim($input['title'][$i]), trim($input['access'][$i]));
	}
	$conf[$field_id] = $output;
}

config::reg_field("sections", "sections", "page_default_sec", array(
	"title" => "Default section",
	"default" => "",
	"desc" => "Leave blank for first defined section",
	"advanced" => true
	)
);
