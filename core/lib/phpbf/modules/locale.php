<?php

/*-------------------------------------------------------*\
|  PHP BasicFramework - Ready to use PHP site framework   |
|  License: LGPL, see LICENSE                             |
\*-------------------------------------------------------*/

/**
* Locale module
* @file locale.php
* @package PhpBF
* @subpackage internationalization
* @version 0.4
* @author Loic Minghetti
* @date Started on the 2007-12-30
*/

// Security
if (!defined('C_SECURITY')) exit;

if (!BF::gc('locale_enabled')) throw new exception('Locale must be enabled in the config file (locale_enabled) in order to use the locale module.');

/**
 * Locale class used to detect internationalization (i18n) and localization (L10n), translate, load predefined language tags
 */
class BF_locale {
	
	/**
	 * @var	string	$lang : language code of selected language
	 */
	public $lang = null;
	/**
	 * @var	string	$country : localization code of selected location
	 */
	public $country = null;
	/**
	 * @var	string	$locale : Locale string (computed from lang and country)
	 */
	public $locale = null;
	
	/**
	 * @var	array	$lang_blocks : define the array of text of loaded predefined language blocks
	 */
	private $lang_blocks = array();
	
	private $languages = array();
	private $countries_by_lang = array();
	
	/**
	 * Constructor to detect language
	 */
	public function __construct() {
		// load and cache language data from config
		$this->countries_by_lang = BF::gc('locale_languages');
		$this->languages = array_keys($this->countries_by_lang);
		 
		// keep track if there have been a change from previous page
		$changed = false;
		
		// DETECT LANGUAGE FIRST
		$lang = null;
		
		// detect lang from URL
		if (!$lang && BF::gc("locale_use_url")) $lang = $this->get_lang_from_url();
		
		// detect lang from GET
		if (!$lang) $lang = $this->get_lang_from_get();
		
		// detect lang from session
		if (BF::gc("locale_use_session")) {
			if (!$lang) $lang = $this->get_lang_from_session();
			elseif ($this->get_lang_from_session() != $lang) $changed = true;
		}
		
		// detect lang from cookie
		if (BF::gc("locale_use_cookie")) {
			if (!$lang) $lang = $this->get_lang_from_cookie();
			elseif ($this->get_lang_from_cookie() != $lang) $changed = true;
		}
		
		// detect lang from user
		if (BF::gc("locale_use_user") && BF::gu()) {
			if (!$lang) $lang = $this->get_lang_from_user();
			elseif ($this->get_lang_from_user() != $lang) $changed = true;
		}
		
		// Try to detect country (lang might still have not been detected yet)
		$country = null;
		
		// detect country from URL
		if (!$country && BF::gc("locale_use_url")) $country = $this->get_country_from_url($lang);
		
		// detect country from GET
		if (!$country) $country = $this->get_country_from_get($lang);
		
		// detect country from session
		if (BF::gc("locale_use_session")) {
			if (!$country) $country = $this->get_country_from_session($lang);
			elseif ($this->get_country_from_session() != $country) $changed = true;
		}
		
		// detect country from cookie
		if (BF::gc("locale_use_cookie")) {
			if (!$country) $country = $this->get_country_from_cookie($lang);
			elseif ($this->get_country_from_cookie() != $country) $changed = true;
		}
		
		// detect country from user
		if (BF::gc("locale_use_user") && BF::gu()) {
			if (!$country) $country = $this->get_country_from_user($lang);
			elseif ($this->get_country_from_user() != $country) $changed = true;
		}
		
		// if lang or country is still undetected, use browser prefered languages and save
		if (!$lang || !$country) {
			$changed = true;
			list($lang, $country) = $this->get_from_browser($lang, $country);
		}
		
		
		// If no lang and/or no country have been detected, then use default
		if (!$lang && $country) {
			$lang = $this->get_lang_from_country($country);
			$changed = true;
		}
		if (!$lang) {
			$country = null;
			$lang = $this->get_lang_from_config();
			$changed = true;
		}
		if (!$country) {
			$country = $this->get_country_from_lang($lang);
			$changed = true;
		}
		
		$this->apply($lang, $country);
		
		// if modifications have been made from last page, save
		if ($changed) $this->save();
	}
	
	/**
	 * PRIVATE. Apply new lang and country to object, and set php locale
	 */
	private function apply($lang, $country) {
		$this->lang = strtolower($lang);
		$this->country = strtoupper($country);
		$this->locale = $this->lang.'_'.$this->country;
		$countries = BF::gc('locale_languages', $this->lang);
		$this->locale_default = $this->lang.'_'.reset($countries);
		
		if (BF::gc('locale_set_php')) {
			// try to set locale. If fails, then no changes should be made, and an error thrown
			if (!setlocale (LC_ALL, 
				$this->locale.'.'.BF::gc('encoding'),	// best, works on linux with proper packages
				$this->locale,
				$this->locale_default.'.'.BF::gc('encoding'),
				$this->locale_default,
				$this->lang.'.'.BF::gc('encoding'),
				$this->lang
			)) throw new exception('Failed setting php locale for locale="'.$this->locale.'". Make sure your serveur allows this locale (or the default one given in config)');
		}
	}
	
	

	/* SET OF FUNCTION TO LOAD LANG AND COUNTRY */
	
	public function get_lang_from_url() {
		return isset($_GET['detected_locale'])? $this->parse_lang($_GET['detected_locale']) : null;
	}
	public function get_lang_from_get() {
		return isset($_GET[BF::gc("locale_get_param_name")])? $this->parse_lang($_GET[BF::gc("locale_get_param_name")]) : null;
	}
	public function get_lang_from_session() {
		return isset($_SESSION['BF_'.BF::gc('project_id').'_lang'])? $_SESSION['BF_'.BF::gc('project_id').'_lang']: null;
	}
	public function get_lang_from_cookie() {
		return isset($_COOKIE['locale'])? $this->parse_lang($_COOKIE['locale']) : null;
	}
	public function get_lang_from_user() {
		return BF::gu()? BF::gu()->locale_lang : null;
	}
	public function get_lang_from_country ($country) {
		foreach ($this->countries_by_lang as $lang => $countries) {
			foreach ($countries as $c) {
				if ($country == $c) return $lang;
			}
		}
		return null;
	}
	public function get_lang_from_config () {
		if (count($this->languages) == 0) throw new BF_internal("You need to set at least one language in config file in order to use internationalization");
		return reset($this->languages);
	}
	public function get_country_from_url($lang = null) {
		return isset($_GET['detected_locale'])? $this->parse_country($_GET['detected_locale'], $lang) : null;
	}
	public function get_country_from_get($lang = null) {
		return isset($_GET[BF::gc("locale_get_param_name")])? $this->parse_country($_GET[BF::gc("locale_get_param_name")], $lang) : null;
	}
	public function get_country_from_session($lang = null) {
		return isset($_SESSION['BF_'.BF::gc('project_id').'_country'])? $this->parse_country($_SESSION['BF_'.BF::gc('project_id').'_country'], $lang): null;
	}
	public function get_country_from_cookie($lang = null) {
		return isset($_COOKIE['locale'])? $this->parse_country($_COOKIE['locale'], $lang) : null;
	}
	public function get_country_from_user($lang = null) {
		return BF::gu()? $this->parse_country(BF::gu()->locale_country, $lang) : null;
	}
	public function get_country_from_lang ($lang) {
		if (!isset($this->countries_by_lang[$lang])) throw new BF_internal("Trying to load countries from unknown language");
		if (count($this->countries_by_lang[$lang]) == 0) throw new BF_internal("Trying to load countries from a language that has no matching countries yet");
		return reset($this->countries_by_lang[$lang]);
	}
	public function get_country_from_config ($lang = null) {
		return $this->get_country_from_lang($lang? $lang : reset($this->languages));
	}
	/**
	 * Detect browser accepted lang and country (and optionaly choose one to match given language OR country)
	 * @param	string	$lang [optional default null] : If lang is given, country will have to match lang
	 * @param	string	$country [optional default null] : If country is given, lang will have to match
	 * @return	array	Array containing lang on index 0 and country on index 1. Each will be null if not detected
	 */
	public function get_from_browser($given_lang = null, $given_country = null) {
		$first_lang = $first_country = null;
		foreach (BF::gb('Languages') as $locale) {
			// sanitize locale
			if (strpos($locale, ';')) $locale = substr($locale, 0, strpos($locale, ';'));
			
			$lang = $this->parse_lang($locale);
			$country = $this->parse_country($locale);
				
			if ($given_lang) {
				if ($country && $this->lang_country_match($given_lang, $country)) return array($given_lang, $country);
			} elseif ($given_country) {
				if ($lang && $this->lang_country_match($lang, $given_country)) return array($lang, $given_country);
			} else {
				if ($lang && $country && $this->lang_country_match($lang, $country)) return array($lang, $country);
				if ($lang) {
					if ($first_country && $this->lang_country_match($lang, $first_country)) return array($lang, $first_country);
					if (!$first_lang) $first_lang = $lang;
				}
				if ($country) {
					if ($first_lang && $this->lang_country_match($first_lang, $country)) return array($lang_lang, $country);
					if (!$first_country) $first_country = $country;
				}
			}
		}
		if ($first_lang) return array($first_lang, null);
		if ($first_country) return array(null, $first_country);
		return array($given_lang, $given_country);
	}
	
	/**
	 * Function to parse a valid lang from a locale
	 * @param	string	$locale : Locale string (eg. en_US). Note, this can be only a language or a country (eg: 'en' or 'US' instead of 'en_US').
	 * @warning	Language must be in lower case and be the first two charaters of locale
	 * @return	mixed	Returns valid lang or null if invalid
	 */
	private function parse_lang($locale) {
		$locale = trim($locale);
		if (strlen($locale) < 2) return null;
		$lang = substr($locale, 0, 2);
		if (strtolower($lang) != $lang) return null;
		return in_array($lang, $this->languages)? $lang : null;
	}
	
	/**
	 * Function to parse a valid country from a locale, optionly matched with a given language
	 * @param	string	$locale : Locale string (eg. en_US). Note, this can be only a language or a country (eg: 'en' or 'US' instead of 'en_US').
	 * @param	string	$lang [optional default null] : Check that country is valid for this language, if not null
	 * @warning	Language must be in lower case while country code must be in upper case, unless it is in given with a langue, ie this is valid : 'en_us', but 'us' is not
	 * @return	mixed	Returns valid country or null if invalid
	 */
	private function parse_country($locale, $lang = null) {
		$locale = trim($locale);

		$country = $locale;
		if (strlen($locale) >= 5) $country = strtoupper(substr($locale, 3, 2));
		if (strtoupper($country) != $country) return null;
		if ($lang) return $this->lang_country_match($lang, $country)? $country : null;
		foreach ($this->countries_by_lang as $lang => $countries) {
			if (in_array($country, $countries)) return $country;
		}
		return null;
	}
	
	/**
	 * Check if a lang country pair match
	 * @param	string	$lang : also check that country is available for this language. Must be 2 lower case letter code and a valid lang
	 * @param	string	$country : 2 upper case letter code
	 * @return	bool
	 */
	public function lang_country_match($lang, $country) {
		return in_array($country, $this->countries_by_lang[$lang]);
	}
	
	/**
	 * Parse a locale and return best possible match given current locale
	 * @param	string	$locale : Locale string (eg. en_US). Note, this can be only a language or a country (eg: 'en' or 'US' instead of 'en_US').
	 * @return	array	Array containing lang on index 0 and country on index 1. Each will be null if not detected
	 */
	public function parse_locale($locale) {
		$lang = $this->parse_lang($locale);
		$country = $this->parse_country($locale);
		if ($this->lang_country_match($lang, $country)) return array($lang, $country);
		if ($lang && $this->lang_country_match($lang, $this->country)) return array($lang, $this->country);
		if ($lang) return array($lang, $this->get_country_from_lang($lang));
		if ($country && $this->lang_country_match($this->lang, $country)) return array($this->lang, $country);
		if ($country) return array($this->get_lang_from_country($country), $country);
		return array($this->lang, $this->country);
	}

	/**
	 * Change locale during runtime
	 * @param	string	$locale : set a locale (eg. 'en_US'), or a partial locale (eg. 'en' or 'US').
	 * @warning	Lang code should always be in lower case, while country code in upper case, unless you specify both, in which cse lower case country is still valid, ie 'en_us' is valid but 'us' is not
	 * @return	bool	True is locale changed from previous, false if same or failed
	 */
	public function set($locale) {
		// detect and combine with current
		list($lang, $country) = $this->parse_locale($locale);
		if ($lang == $this->lang && $country == $this->country) return false;
		$this->apply($lang, $country);
		// save
		$this->save();
	}
	
	/**
	 * Used to save modifications made to locale to session, cookie, and/or user database
	 */
	public function save() {
		if (BF::gc("locale_use_session")) $this->save_to_session();
		if (BF::gc("locale_use_cookie")) $this->save_to_cookie();
		if (BF::gu()) $this->save_to_user();
	}
	public function save_to_session() {
		$_SESSION['BF_'.BF::gc('project_id').'_lang'] = $this->lang;
		$_SESSION['BF_'.BF::gc('project_id').'_country'] = $this->country;
	}
	public function save_to_cookie() {
		if (!isset($_COOKIE['locale']) || $_COOKIE['locale'] != $this->locale) {
			setcookie('locale', $this->locale, time()+365*60*60*24, '/');
		}
	}
	public function save_to_user() {
		// save to user object (DB) if needed
		if (BF::gu() && isset(BF::gu()->locale) && BF::gu()->locale != $this->locale) {
			BF::gu()->locale = $this->locale;
			BF::gu()->save();
		}
	}
	

	
	/**
	 * Format URL to include locale is necessary
	 * @param	string	$base_url : Site base url
	 * @param	string	$path : URL Path
	 * @param	string	$locale [optional default null] : Set a different locale
	 */
	public function format_url ($base_url, $path, $locale = null) {
		list($lang, $country) = $locale? $this->parse_locale($locale) : array($this->lang, $this->country);
		if (BF::gc('locale_use_url')) {
			switch (BF::gc('locale_url_syntax')) {
				case 'lang-country' : return $base_url.$lang.'-'.$country."/".$path;
				case 'lang_country' : return $base_url.$lang.'_'.$country."/".$path;
				case 'lang' : return $base_url.$lang."/".$path;
				case 'country' : return $base_url.$country."/".$path;
				default : return $base_url.$path;
			}
		} elseif (BF::gc('locale_use_get') || $lang != $this->lang || $country != $this->country) {
			if (!$locale) $locale = $this->locale;
			$url = $base_url.$path;
			if (strpos($url, '?') !== false) {
				$pos = strpos($url, '#');
				if ($pos !== false) return substr($url, 0, $pos).'&locale='.$locale.substr($url, $pos);
				else return $url.'&locale='.$locale;
			} else {
				return $url.'?locale='.$locale;
			}
		} else return $base_url.$path;
	}
	
	/**
	 * Translate a block of text in set language, country or locale
	 * @param	string	$text : Text to translate. May be either contain multilangual blocks, or predefined text blocks with parameters.
	 */
	public function tl($text) {
		// Look for multilangual blocks (eg. [en:Hello![fr:Bonjour!]) and predefined blocks (eg. [xx:lang_entry])
		return preg_replace_callback('/\[([a-z]{2}|[A-Z]{2}|00|[a-z]{2}_[A-Z]{2})\:(((\\\\\])|(\\\\\[)|[^\[\]])*)(\]|)/', array($this, '_tl_replace'), $text);
	}
	
	private function _tl_replace($match) {
		return str_replace(array("\\[","\\]"), array("[","]"), $this->tl_tag($match[2], $match[1]));
	}
	
	/**
	 * Process a language tag
	 * @param	string	$text : Text corresponding to the tag
	 * @param	string	$tag [optioal default 'xx'] : name of the language, country or locale tag read, or xx for a predefined text block
	 * @return	string	Text to display
	 */
	public function tl_tag($text, $tag = 'xx') {
		if ($tag == 'xx') {
			$attr = explode('|', $text);
			if (!array_key_exists($attr[0], $this->lang_blocks)) {
				if (strpos($attr[0], '.') === false) throw new exception('Name of predefined language blocks must either contain a valid translation pack reference (before the \'.\') in order to detect file to load, or be manualy loaded before being used');
				$this->load_lang_blocks(substr($attr[0], 0, strpos($attr[0], '.')));
				if (!array_key_exists($attr[0], $this->lang_blocks)) return '['.$text.']';
			}
			$attr[0] = $this->lang_blocks[$attr[0]];
			return @call_user_func_array('sprintf', $attr);
		} elseif ($tag == $this->lang || $tag == $this->country || $tag == $this->locale) {
			return $text;
		} else {
			return '';
		}
	}
	
	/**
	 * Returns whever a predefined text block is defined or not
	 * @param	string	$block : Name of the block
	 * @return	bool	true if exists, false otherwise
	 */
	public function block_exists ($block) {
		if (array_key_exists($block, $this->lang_blocks)) return true;
		$this->tl_tag($block);
		return array_key_exists($block, $this->lang_blocks);
	}
	
	/**
	 * Read and load a language file listing predefined language blocks
	 * @param	string	$translation_pack : Name of the translation pack to load from the lang folder)
	 */
	private function load_lang_blocks($translation_pack) {
		// check if file has already been loaded or not
		static $loaded = array();
		if (in_array($translation_pack, $loaded)) return;
		$loaded[] = $translation_pack;
		$file = $translation_pack.'.'.$this->lang.'.tl';
		if (!BF::gf('translations')->exists($file)) return false; //throw new exception('Could not find predefined language file : '.$file);
		$entries = file(BF::gf('translations')->path().$file);
		for ($i = 0; $i < count($entries); $i++) {
			// if line start with $, then it is a new entry (otherwise, might be a syntax error or a comment)
			$entries[$i] = trim($entries[$i]);
			if (!isset($entries[$i][0]) || $entries[$i][0] != '$') continue;
			$tag = '';
			$value = '';
			list($tag, $value) = explode('=', $entries[$i], 2);
			// an entry could span on more than one line
			while ($i+1 < count($entries) && $entries[$i+1][0] != '$') {
				$value .= $entries[$i+1][0];
				$i++;
			}
			$this->lang_blocks[substr(trim($tag), 1)] = trim($value);
		}
	}
	
	/**
	 * Create a localized version of a file. Will trigger fatal error on failure
	 * @param	object	$folder : Folder containing the file
	 * @param	string	$filename : Name of the file to localize, without the extension
	 * @param	string	$extension [optional default 'php'] : Extension of the file, null or empty for no extension
	 * @return	bool	true
	 */
	public function localize_file($type, $folder, $filename, $extension = 'php') {
		if ($type != 'locale' && $type != 'lang' && $type != 'country') throw new exception('Localized type must be either lang, country or locale');
		$source = file_get_contents($folder->path().$filename.($extension? '.'.$extension:''));
		if ($source === FALSE) throw new exception('Failed reading source file for localization, path : '.$folder->path().$filename.($extension? '.'.$extension:''));
		if ($extension == 'php') $source = '<?PHP /* DO NOT EDIT THIS FILE, THIS IS THE LOCALIZED VERSION OF '.$filename.'.php */ ?>'.$source;
		if (file_put_contents($folder->path().$filename.'.'.$this->$type.($extension? '.'.$extension:''), $this->tl($source)) === FALSE) throw new exception('Failed writing new file after localization, path : '.$folder->path().$filename.'.'.$this->$type.($extension? '.'.$extension:''));
		return true;
	}
	
	/**
	 * Function that should be called in all localized php scripts, at the beginning
	 * @param	bool	$is_original : Whether this has been called in original file or a localized. In order to detect this, this parameter should be set to :  0\*[00:*\+1\*]*\ (note: replace \ by /)
	 * @param	string	$type : What type of versioning for this file, 'lang', 'country', or 'locale'
	 * @param	object	$folder : Folder containing the file
	 * @param	string	$filename : Name of the file to localize, without the extension
	 * @param	string	$extension [optional default 'php'] : Extension of the file, null or empty for no extension
	 * @return	bool	true if reading of the file should continue, false if included file need to stop and return, in which case the localized version of the file will be called instead
	 * @warning	Caution, if you have functions or classes declared in thelocalized script, make sure you don't define them if this function return false, otherwise, they might get declared twice
	 */
	public function load_localized($is_original, $type, $folder, $filename, $extension = 'php') {
		// if in original file and localized version has not been made yet OR if version is out of date, make localized version
		if ($type != 'locale' && $type != 'lang' && $type != 'country') throw new exception('Localized type must be either lang, country or locale');
		if (
			($is_original && !$folder->exists($filename.'.'.$this->$type.($extension? '.'.$extension:'')))
			||
			(filemtime($folder->path().$filename.'.'.$this->$type.($extension? '.'.$extension:'')) < filemtime($folder->path().$filename.($extension? '.'.$extension:'')))
		) {
			$this->localize_file($type, $folder, $filename, $extension);
			$folder->load($filename, $this->$type, $extension);
			return false;
		} elseif ($is_original) {
			$folder->load($filename, $this->$type, $extension);
			return false;
		} else {
			return true;
		}
	}
}




?>
