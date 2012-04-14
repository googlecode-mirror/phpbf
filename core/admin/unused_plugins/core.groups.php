<?php

/**
 * Configuration file for groups and access restriction
 */


config::reg_section("groups", array("title" => "Groups"));

config::reg_subsection("groups", "mode", array("title" => "Groups mode"));
config::reg_subsection("groups", "builtin", array("title" => "Built-in mode only"));


//// mode

config::reg_field("groups", "mode", "user_group", array(
	"title" => "Group mode",
	"type" => "radio",
	"options" => array(1 => "Built-in", 2 => "Database", 0 => "None"),
	"default" => 1,
	"cast" => "int",
	"desc" => "Determine how user are sorted into groups. See doc for more info"
	)
);


//// builtin

config::reg_field("groups", "builtin", "user_field_id_group", array(
	"title" => "User table group field name",
	"type" => "text",
	"default" => "id_group",
	"desc" => "Name of the user table field containing the group id. Field must be of type integer with length 2"
	)
);
config::reg_field("groups", "builtin", "groups", array(
	"title" => "List of groups",
	"type" => "table",
	"columns" => array(
		"id" => array("title"=>"ID", "type"=>"custom", "width"=>"10%", "content" => "<b>%s</b>"),
		"name" => array("title"=>"Name", "width"=>"30%"),
		"parents" => array("title"=>"Parent groups (names, seperated by |)")
	),
	"num" => 32,
	"onsave" => "config_groups_table_save",
	"onload" => "config_groups_table_load",
	"default" => array(),
	"desc" => "Each user will be associated to a single group ID, but will also belongs to the group's parents, and will inherite their rights"
	)
);


function config_groups_table_load ($id, $raw) {
	$groups_names = is_array(config::get('groups'))? config::get('groups') : array();
	for ($i = 1; $i <= 32; $i++) {
		$output[$i-1] = array("id" => $i, "name" => $groups_names[$i], "parents" => array()); 
		for ($j = 1; $j <= 32; $j++) {
			if (config::get('groups_parents', $i) & pow(2, $j-1)) $output[$i-1]["parents"][] = $groups_names[$j];
		}
	}
	return $output;
}

function config_groups_table_save ($id, $input, &$conf) {
	// first add all group names
	$conf["groups_id"] = array();
	for ($i = 1; $i <= 32; $i++) {
		if (trim($input["name"][$i-1]) != '') {
			$conf["groups_id"][strtolower(trim($input["name"][$i-1]))] = $i;
			$conf["groups"][$i] = strtolower(trim($input["name"][$i-1]));
		}
	}
	// built all group parents
	$conf["groups_parents"] = array();
	foreach ($conf["groups_id"] as $name => $id_group) {
		$conf["groups_parents"][$id_group] = 0;
		$parents = explode('|', strtolower($input["parents"][$id_group-1]));
		foreach ($parents as $parent_name) {
			$parent_name = trim($parent_name);
			if (!isset($conf["groups_id"][$parent_name]) || $parent_name == $name) continue;
			$conf["groups_parents"][$id_group] = $conf["groups_parents"][$id_group] | pow(2,$conf["groups_id"][$parent_name]-1);
		}
	}

	$conf["groups_ancestors"] = array();
	foreach ($conf["groups_parents"] as $id_group => $parents) {
		$conf["groups_ancestors"][$id_group] = config_groups_get_ancestors($id_group, $conf);		
	}
}
// built ancestors
function config_groups_get_ancestors($id_group, &$c, $groups_checked = 0) {
	$groups_checked = $groups_checked | pow(2,$id_group-1); // add this group to list of groups already checked
	$ancestors = pow(2,$id_group-1); // list of ancestors for this group (start by adding self)
	// get parents
	for ($i = 1; $i <= 32; $i++) {
		// if group $i exists AND has not been checked yet, AND is a parent of current group
		if ( isset($c["groups_parents"][$id_group]) && !($groups_checked & pow(2,$i-1)) && $c["groups_parents"][$id_group] & pow(2,$i-1)) {
			$ancestors = $ancestors | config_groups_get_ancestors($i, $c, $groups_checked);
		}
	}
	return $ancestors;
}
