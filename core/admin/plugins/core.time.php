<?php

/**
 * Configuration file for time
 */


config::reg_section("time", array("title" => "Time", "advanced" => false));

config::reg_subsection("time", "time", array("title" => "Server time"));


//// time

config::reg_field("time", "time", "time_default_zone", array(
	"title" => "Server time zone",
	"type" => "text",
	"default" => "GMT",
	"desc" => "Default time zone (Works with PHP versions > 5.1). Must be a <a href='http://php.net/manual/en/timezones.php' target='_blank'>valid PHP Timezone</a>"
	)
);
