<?php

/**
 * Configuration file for templates
 */


config::reg_section("template", array("title" => "Templates", "advanced" => false));

config::reg_subsection("template", "content", array("title" => "Pages content and organization"));
config::reg_subsection("template", "smarty", array("title" => "Smarty template engine options"));
config::reg_subsection("template", "caching", array("title" => "Caching"));


//// content

config::reg_field("template", "content", "window_title", array(
	"title" => "Window title",
	"type" => "text",
	"default" => "My site - %s",
	"desc" => "Default browser window title. '%s' will be replaced by current page title"
	)
);
config::reg_field("template", "content", "template_default_creator", array(
	"title" => "Default template creator meta",
	"type" => "text",
	"default" => "My name",
	"desc" => "Default value for the creator meta tag in header. You can also chage this directly in header.tpl"
	)
);
config::reg_field("template", "content", "page_default", array(
	"title" => "Default page",
	"type" => "text",
	"default" => "home",
	"desc" => "Default page to display (site home page)"
	)
);
config::reg_field("template", "content", "page_error", array(
	"title" => "Error page",
	"type" => "text",
	"default" => "error",
	"desc" => "Page to display when an error occurs",
	"advanced" => true
	)
);
config::reg_field("template", "content", "page_logged", array(
	"title" => "Logged welcome page",
	"type" => "text",
	"default" => "logged",
	"desc" => "Page to display once user is logged"
	)
);
config::reg_field("template", "content", "template_default_content_type", array(
	"title" => "Default content type",
	"type" => "text",
	"default" => "text/html",
	"advanced" => true,
	"desc" => "Default content type for all templates served by template"
	)
);


//// Smarty


config::reg_field("template", "smarty", "template_smarty_debug", array(
	"title" => "Smarty debug mode",
	"type" => "checkbox",
	"label" => "Enable",
	"cast" => "bool",
	"default" => false,
	"desc" => "Use Smarty template engine debug mode that display a popup showing all template varaibles assigned on every page"
	)
);
config::reg_field("template", "smarty", "template_compile_check", array(
	"title" => "Check templates for modifications",
	"type" => "radio", // custom, radio, checkbox, text
	"default" => true,
	"options" => array(0 => "Disabled", 1 => "Enabled"),
	"cast" => "bool",
	"desc" => "Upon each invocation of the PHP application, Smarty tests to see if the current template has changed (different time stamp) since the last time it was compiled. If it has changed, it recompiles that template. If the template has not been compiled, it will compile regardless of this setting.",
	"advanced" => true
	)
);



//// Caching

config::reg_field("template", "caching", "template_cache", array(
	"title" => "Caching",
	"type" => "radio",
	"options" => array(0 => "Caching disabled", 1 => "Caching enabled"),
	"default" => false,
	"cast" => "bool",
	"desc" => "Cache templates output for better performance. <br/>Note: Make sure all templates can be cached, most dynamic content cannot be cached and need to be escaped"
	)
);
config::reg_field("template", "caching", "template_cache_lifetime", array(
	"title" => "Cache lifetime",
	"type" => "text",
	"cast" => "int",
	"default" => 86400,
	"desc" => "Default lifetime for cached templates. -1 for unlimited, 0 for regenerate on each load (not recommended, disable caching instead)"
	)
);

