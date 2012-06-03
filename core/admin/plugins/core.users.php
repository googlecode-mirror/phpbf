<?php

/**
 * Configuration file for users
 */


config::reg_section("users", array("title" => "User identification"));

config::reg_subsection("users", "login", array("title" => "User login"));


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



