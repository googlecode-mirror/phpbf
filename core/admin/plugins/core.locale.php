<?php

/**
 * Configuration file for locale
 */


config::reg_section("locale", array("title" => "Locale"));

config::reg_subsection("locale", "options", array("title" => "Localization and internationalization options", "advanced" => true));
config::reg_subsection("locale", "retreive", array("title" => "Locale retreive mode", "advanced" => true));
config::reg_subsection("locale", "langs", array("title" => "Available languages"));


//// mode
config::reg_field("locale", "options", "locale_enabled", array(
	"title" => "Enable translations",
	"type" => "checkbox",
	"label" => "Parse language blocks in pages ",
	"cast" => "bool",
	"default" => true,
	"desc" => "Look for multilanguage tags in page files and translate "
	)
);
config::reg_field("locale", "options", "locale_set_php", array(
	"title" => "Use PHP locale",
	"type" => "checkbox",
	"label" => "Set PHP locale on every page ",
	"cast" => "bool",
	"default" => true,
	"advanced" => true,
	"desc" => "Desactivate if locale is enabled but host does not support php's set_locale "
	)
);
config::reg_field("locale", "retreive", "locale_use_session", array(
	"title" => "Store with session",
	"type" => "checkbox",
	"default" => false,
	"advanced" => true,
	"desc" => "Use session to store locale and retrieve on each page. This is transparent to user"
	)
);
config::reg_field("locale", "retreive", "locale_use_cookie", array(
	"title" => "Store with cookie",
	"type" => "checkbox",
	"default" => true,
	"advanced" => true,
	"desc" => "Use cookie to store locale and retrieve on each page. This will be kept across sessions"
	)
);
config::reg_field("locale", "retreive", "locale_use_url", array(
	"title" => "Store in URL",
	"type" => "checkbox",
	"default" => false,
	"advanced" => true,
	"desc" => "Use URL to store locale and retrieve on each page. Will add a 'virtual' top folder for each language. This is a good solution for search engines."
	)
);
config::reg_field("locale", "retreive", "locale_url_syntax", array(
	"title" => "URL only: syntax",
	"type" => "radio",
	"options" => array("lang_country" => "lang_country (eg. /en_US/)", "lang-country" => "language-country (eg. /en-US/)", "lang" => "language only (eg. /en/)", "country" => "country only (eg. /US/)"),
	"default" => "lang",
	"advanced" => true,
	"desc" => "Define here how to write locale in the URL. Note that this does not affect URL parsing, all syntax are valid for setting the locale"
	)
);
config::reg_field("locale", "retreive", "locale_use_get", array(
	"title" => "Store in GET params",
	"type" => "checkbox",
	"default" => false,
	"advanced" => true,
	"desc" => "Use GET params to store locale and retrieve on each page. This is not as nice as in URL storing but can be used if in URL is not possible."
	)
);


config::reg_field("locale", "retreive", "locale_get_param_name", array(
	"title" => "GET param only: locale param name",
	"type" => "text",
	"default" => "locale",
	"advanced" => true,
	"desc" => "If using GET param to store locale, then define the name of the GET param"
	)
);
config::reg_field("locale", "retreive", "locale_use_user", array(
	"title" => "Store in user DB",
	"type" => "checkbox",
	"default" => false,
	"advanced" => true,
	"desc" => "Use user DB to store logged users locale and retrieve on each page. This will be kept across sessions. User object must have locale_lang and locale_country fields (which can either be real DB entries or an overloaded fields)"
	)
);


//// langs

config::reg_field("locale", "langs", "locale_languages", array(
	"title" => "Languages",
	"type" => "table",
	"columns" => array(
		"lang" => array("title"=>"Language code", "width"=>"30%"),
		"countries" => array("title"=>"Countries code")
	),
	"num" => "auto",
	"onsave" => "config_langs_table_save",
	"onload" => "config_langs_table_load",
	"default" => array("lang" => array(0=>"en"), "countries" => array(0 => "US|UK")),
	"example" => array("lang"=>"en", "countries"=>"US|UK|CA|SG"), 
	"desc" => "Table of all languages available (2 letters ID). First will be default if detection fails.<br/>For each language: list of all countries available (2 letters ID), separated by |. First will be default if detection fails.<br/>Warning: If you use php locales (see Use PHP locale in advanced locale options), each pair of language / first country, must be a valid PHP locale, otherwise, the language won't be available and an error will be thrown"
	)
);


function config_langs_table_load ($field_id, $raw) {
	$output = array();
	if (is_array($raw)) {
		foreach ($raw as $lang => $countries) {
			$output[] = array("lang" => $lang, "countries" => $countries);
		}
	}
	return $output;
}

function config_langs_table_save ($field_id, $input, &$conf) {
	$output = array();
	for ($i=0; $i < count($input['lang']); $i++) {
		$id = strtolower(trim($input['lang'][$i]));
		if (strlen($id) == 2) {
			$output[$id] = array_values(array_filter(array_map("trim", explode("|", strtoupper($input['countries'][$i])))));
		}
	}
	$conf[$field_id] = $output;
}
