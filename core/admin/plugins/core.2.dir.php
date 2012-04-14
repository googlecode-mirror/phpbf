<?php

/**
 * Configuration file for directories
 */


config::reg_section("dir", array("title" => "Directories"));

config::reg_subsection("dir", "base_url", array("title" => "Base URL", "advanced" => true, "desc" => "Website root will not always match server document root, in which case the framework needs to know its base URL"));

config::reg_subsection("dir", "path_to_root", array("title" => "Framework folder", "advanced" => true));

config::reg_subsection("dir", "builtin", array("title" => "Built-in directories", "desc"=> '
	Note : All folder paths should end with a trailing slash /. A valid folder path is one of the following:
	<ul>
    	<li>A relative path from the site root directory (where index files is located). It must NOT start with a slash /</li>
    	<li>An absolute path on server starting with a slash /</li>
    	<li>An URL in the form scheme://... (example: <i>http://data.mysite/imagedb/</i> for <i>img</i> folder to host your images elsewhere)</li>
    </ul>
    Do not use backward slashs \\, unless you only use windows based server. Forward slash / always work and is recommended.
'));
config::reg_subsection("dir", "userdefined", array("title" => "User-defined folders"));


//// base_url

config::reg_field("dir", "base_url", "auto_detect_base_url", array(
	"title" => "Autodetect base URL",
	"type" => "radio",
	"options" => array(1 => "Automatic", 0 => "Manual"),
	"default" => true,
	"cast" => "bool",
	"advanced" => true,
	"desc" => "Detection need urlrewrite to be on and .htaccess file from the website root to be processed. Make sure you placed the right .htaccess in website root. Depending on server configuration you might need to set it manually."
	)
);
config::reg_field("dir", "base_url", "base_url", array(
	"title" => "Manual Base URL",
	"default" => "/",
	"advanced" => true,
	"desc" => "Base URL to website root. Must be an absolute path, must start and end with a /"
	)
);

//// path_to_root

config::reg_field("dir", "path_to_root", "path_to_root", array(
	"title" => "Path to root directory",
	"default" => "./../../../",
	"desc" => "Path to site root directory. Must either be a relative path from Framework.php to the root folder (where index files is located) or an absolute path on the server. Must end with a /."
	)
);

//// builtin
config::reg_field("dir", "builtin", "folder_framework", array(
	"title" => "Folder : framework",
	"default" => "core/lib/phpbf/",
	"desc" => "Folder containing PhpBF Framework (where framework.php and conf.php are)"
	)
);
config::reg_field("dir", "builtin", "folder_admin", array(
	"title" => "Folder : admin",
	"default" => "core/admin/",
	"desc" => "Folder containing the administration console"
	)
);
config::reg_field("dir", "builtin", "folder_tpl", array(
	"title" => "Folder : tpl",
	"default" => "core/templates/",
	"desc" => "Folder containing templates"
	)
);
config::reg_field("dir", "builtin", "folder_data", array(
	"title" => "Folder : data",
	"default" => "core/data/",
	"desc" => "Folder with write permission (for compiled templates, file DB, etc...)"
	)
);
config::reg_field("dir", "builtin", "folder_log", array(
	"title" => "Folder : log",
	"default" => "core/data/log/",
	"desc" => "Folder containing log file (must be writable)"
	)
);
config::reg_field("dir", "builtin", "folder_compiled", array(
	"title" => "Folder : compiled",
	"default" => "core/data/compiled_templates/",
	"desc" => "Folder containing compiled templates (must be writable) "
	)
);
config::reg_field("dir", "builtin", "folder_cached", array(
	"title" => "Folder : cached",
	"default" => "core/data/cached_templates/",
	"desc" => "Folder containing cached templates (must be writable) "
	)
);
config::reg_field("dir", "builtin", "folder_modules", array(
	"title" => "Folder : modules",
	"default" => "core/lib/phpbf/modules/",
	"desc" => "Folder containing modules "
	)
);
config::reg_field("dir", "builtin", "folder_model", array(
	"title" => "Folder : model",
	"default" => "core/model/",
	"desc" => "Folder containing data model "
	)
);
config::reg_field("dir", "builtin", "folder_smarty", array(
	"title" => "Folder : smarty",
	"default" => "core/lib/smarty/",
	"desc" => "Folder containing Smarty template engine"
	)
);
config::reg_field("dir", "builtin", "folder_tags", array(
	"title" => "Folder : tags",
	"default" => "core/tags/",
	"desc" => "Folder containing tags for smarty"
	)
);
config::reg_field("dir", "builtin", "folder_translations", array(
	"title" => "Folder : translations",
	"default" => "core/translations/",
	"desc" => ""
	)
);
config::reg_field("dir", "builtin", "folder_img", array(
	"title" => "Folder : img",
	"default" => "img/",
	"desc" => "Folder containing images "
	)
);
config::reg_field("dir", "builtin", "folder_css", array(
	"title" => "Folder : css",
	"default" => "css/",
	"desc" => "Folder containing static stylesheets"
	)
);
config::reg_field("dir", "builtin", "folder_js", array(
	"title" => "Folder : js",
	"default" => "js/",
	"desc" => "Folder containing javascript's scripts"
	)
);
config::reg_field("dir", "builtin", "folder_db", array(
	"title" => "Folder : db",
	"default" => "core/data/db/",
	"desc" => "Folder containing file database like sqlite"
	)
);
config::reg_field("dir", "builtin", "folder_lib", array(
	"title" => "Folder : lib",
	"default" => "core/lib/",
	"desc" => "Lib folder containing third party php libraries"
	)
);


//// user-defined

config::reg_field("dir", "userdefined", "userdefined_folders", array(
	"title" => "User-defined folders",
	"type" => "table",
	"columns" => array(
		"id" => array("title"=>"ID", "width"=>"30%"),
		"path" => array("title"=>"Path")
	),
	"num" => "auto",
	"onsave" => "config_folders_table_save",
	"onload" => "config_folders_table_load",
	"default" => array(),
	"example" => array("id"=>"mydocs", "path"=>"/home/X/Public/docs/"), 
	"desc" => "You can define your owne folder ID, that can be used in the same way as the built in folders."
	)
);


function config_folders_table_load ($field_id, $raw) {
	$output = array();
	foreach (config::search('/^folder_/') as $id => $path) {
		foreach (config::$fields["dir"]["builtin"] as $builtin_id => $builtin_path) {
			if ($builtin_id == $id) continue(2);
		}
		$output[] = array("id" => substr($id, 7), "path"=>$path);
	}
	return $output;
}

function config_folders_table_save ($field_id, $input, &$conf) {
	for ($i=0; $i< count($input['id']); $i++) {
		$id = trim($input['id'][$i]);
		if ($id) {
			$conf["folder_".$id] = trim($input['path'][$i]);
		}
	}
}
