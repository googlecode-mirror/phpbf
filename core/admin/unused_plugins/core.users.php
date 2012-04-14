<?php

/**
 * Configuration file for users
 */


config::reg_section("users", array("title" => "User identification"));

config::reg_subsection("users", "login", array("title" => "User login"));
config::reg_subsection("users", "database", array("title" => "User table in database", "advanced"=> true, "desc" => "Users data should be stored in table with alias 'users' as defined in the database section. Name of required fields can be changed below"));


//// login

config::reg_field("users", "login", "user_login_time", array(
	"title" => "Max logged time",
	"default" => "24",
	"desc" => "Time in hours before login expire and user has to login again. 0 for never"
	)
);
config::reg_field("users", "login", "user_inactivity_time", array(
	"title" => "Max inactivity time",
	"default" => "5",
	"desc" => "Time in hours of inactivity before login expire and user has to login again. 0 for never "
	)
);

//// database

config::reg_field("users", "database", "user_field_username", array(
	"title" => "Username field",
	"default" => "username",
	"desc" => "Name of the table field containing the unique user name"
	)
);
config::reg_field("users", "database", "user_field_password", array(
	"title" => "Password MD5 field",
	"default" => "password_md5",
	"desc" => "Name of the table field containing the md5 checksum of the user's password<br/>Field must be char(32)"
	)
);
config::reg_field("users", "database", "user_field_date_login", array(
	"title" => "Time of login field",
	"default" => "date_login",
	"desc" => " Name of the table field containing the UNIX timestamp of the last login<br/>Field must be int(10)"
	)
);


