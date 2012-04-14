<?php

/**
 * Configuration file for time
 */


config::reg_section("db", array("title" => "Database"));

config::reg_subsection("db", "connections", array("title" => "Database connections"));

config::reg_subsection("db", "tables", array("title" => "Database tables"));


//// connections

config::reg_field("db", "connections", "db", array(
	"title" => "Connections",
	"type" => "table",
	"columns" => array(
		"id" => array("title"=>"ID", "width"=>"15%", "type"=>"text"),
		"type" => array("title"=>"Type", "width"=>"15%","type"=>"text"),
		"db" => array("title"=>"Database", "width"=>"20%", "type"=>"text"),
		"host" => array("title"=>"Host", "width"=>"20%", "type"=>"text"),
		"user" => array("title"=>"Username", "width"=>"15%", "type"=>"text"),
		"pass" => array("title"=>"Password","width"=>"15%", "type"=>"text")
	),
	"num" => "auto",
	"onsave" => "config_db_table_save",
	"onload" => "config_db_table_load",
	"default" => array(),
	"example" => array("id"=>"db_loc", "type"=>"mysql", "db"=>"mydb", "host"=>"localhost", "user"=>"myusername", "pass"=>"mypassword"), 
	"desc" => "All DB connections must be defined here.<br/>The ID must be unique and not contain spaces.<br/>The type is the DB type, for example : mysql, sqlite, etc... The required modules will be loaded."
	)
);


function config_db_table_load ($field_id, $raw) {
	$output = array();
	if (is_array($raw)) {
		foreach ($raw as $id => $entry) {
			$output[] = array("id" => $id, "type"=>$entry[0], "db"=>$entry[1], "host"=>$entry[2], "user"=>$entry[3], "pass"=>$entry[4]);
		}
	}
	return $output;
}

function config_db_table_save ($field_id, $input, &$conf) {
	$output = array();
	for ($i=0; $i< count($input['id']); $i++) {
		$id = trim($input['id'][$i]);
		if ($id && trim($input['type'][$i])) {
			$output[$id] = array(trim($input['type'][$i]), trim($input['db'][$i]), trim($input['host'][$i]), trim($input['user'][$i]), $input['pass'][$i]);
		}
	}
	$conf[$field_id] = $output;
}

/// tables

config::reg_field("db", "tables", "tables", array(
	"title" => "Tables",
	"type" => "table",
	"columns" => array(
		"alias" => array("title"=>"Alias", "width"=>"15%", "type"=>"text"),
	//	"db" => array("title"=>"ID DB connection", "width"=>"15%","type"=>"text"),
		"name" => array("title"=>"Table real name", "width"=>"15%", "type"=>"text"),
	//	"fields" => array("title"=>"Fields (to load by default)", "type"=>"text"),
	//	"primary" => array("title"=>"Primary key", "width"=>"10%", "type"=>"text")
	),
	"num" => "auto",
	"onsave" => "config_tables_table_save",
	"onload" => "config_tables_table_load",
	"default" => array(),
	"example" => array("alias"=>"user", "db"=>"db_loc", "name"=>"someprefix_user", "fields"=>"id|username|password|email", "primary"=>"username"), 
	"desc" => "All DB tables must be defined here.<br/>The alias must be unique and not contain spaces.<br/>The ID DB is the ID of the DB connection to use. It must be defined in the abrove table.<br/>The real name of the table, as it can be found in the database, must be griven under table name. The fields must contain the list of all fields to load by default. Use | to separate entries. If blank all fields will be loaded.<br/>Give name of primary key field, to identify each row"
	)
);


function config_tables_table_load ($field_id, $raw) {
	$output = array();
	if (!isset($raw['users'])) $output[] = array("alias" => "users");
	if (!isset($raw['groups'])) $output[] = array("alias" => "groups");
	if (!isset($raw['users_groups'])) $output[] = array("alias" => "users_groups");
	if (is_array($raw)) {
		foreach ($raw as $id => $entry) {
			//$output[] = array("alias" => $id, "db"=>$entry[0], "name"=>$entry[1], "fields"=>$entry[2], "primary"=>$entry[3]);
			$output[] = array("alias" => $id, "name"=>$entry);
		}
	}
	return $output;
}

function config_tables_table_save ($field_id, $input, &$conf) {
	$output = array();
	for ($i=0; $i< count($input['alias']); $i++) {
		$id = trim($input['alias'][$i]);
		if ($id) {
			//$output[$id] = array(trim($input['db'][$i]), trim($input['name'][$i]), array_values(array_filter(array_map("trim", array_map("strtolower", explode("|", $input['fields'][$i]))))), trim($input['primary'][$i]));
			$output[$id] = trim($input['name'][$i]);
		}
	}
	$conf[$field_id] = $output;
}
