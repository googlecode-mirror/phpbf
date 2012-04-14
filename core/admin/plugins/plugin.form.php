<?php

/**
 * Configuration file for directories
 */
config::reg_field("dir", "builtin", "folder_forms", array(
	"title" => "Folder : forms",
	"default" => "core/forms/",
	"desc" => "Folder containing form data (*.frm) "
	)
);
config::reg_field("dir", "builtin", "folder_compiled_forms", array(
	"title" => "Folder : compiled_forms",
	"default" => "core/data/compiled_forms/",
	"desc" => "Folder containing compiled form data (must be writable) "
	)
);
