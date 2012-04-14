<?php

/**
 * Configuration file for directories
 */

config::reg_field("dir", "builtin", "folder_dcss", array(
	"title" => "Folder : dcss",
	"default" => "Framework/DynamicCSS/",
	"desc" => "Folder containing dynamic stylesheets source (*.dcss) "
	)
);
config::reg_field("dir", "builtin", "folder_dcss_compiled", array(
	"title" => "Folder : dcss_compiled",
	"default" => "Stylesheets/dynamic/",
	"desc" => "Folder containing parsed dynamic stylesheets (*.css). Must be writable "
	)
);
config::reg_field("template", "content", "dynamic_stylesheet_base_url", array(
	"title" => "Dynamic stylesheet base URL",
	"type" => "text",
	"default" => "./../../",
	"advanced" => true,
	"desc" => "Base url to use for links in dynamicly generated stylesheets. Can be the relative path from dcss_compiled folder to root folder. Change from default only if you moved the dcss_compiled folder"
	)
);
