<?php


class test {
	static function ok ($text = "OK") {
		common::flush();
		return '<span style="color: green;"><b>'.$text.'</b></span>';
	}
	static function warning($text = 'Warning') {
		common::flush();
		return '<span style="color: orange;"><b>'.$text.'</b></span>';
	}
	static function invalid($text = 'Invalid') {
		common::flush();
		return '<span style="color: red;"><b>'.$text.'</b></span>';
	}
	
	static function updatable() {
		return test::framework_working()
			&& test::config_writable()
			&& test::root_path_write_valid()
			&& test::admin_data_writable()
			&& test::directories_updatable();
	}
	
	static function framework_working() {
		return test::get_php_version() > 5
			&& test::config_exists()
			&& test::config_complete()
			&& test::root_path_valid()
			&& test::directories_exist()
			&& test::directories_writable();
	}
	
	static function get_apache_version() {
		if (function_exists("apache_get_version")) return apache_get_version();
		else return false;
	}
	static function get_php_version() {
		return (float) preg_replace('/[a-zA-Z-]/', '', phpversion());
	}
	static function session_enabled() {
		return extension_loaded('session');
	}
	static function magic_quotes_on() {
		return get_magic_quotes_gpc();
	}
	static function config_exists() {
		return file_exists(CONFIG_FILE);
	}
	static function config_writable() {
		return file_exists(CONFIG_FILE_WRITE) && is_writable(CONFIG_FILE_WRITE);
	}
	static function config_complete() {
		if (config::config_exists()) return config::is_complete();
		else return false;
	}
	static function root_path_valid() {
		// if config file exists, then best is to compare both paths point to the same config file
		if (config::config_exists()) {
			return get_file("admin", "include/class.test.php")->read() == file_get_contents(__FILE__);
		}
		return @is_dir(PATH_TO_ROOT);
	}
	static function root_path_write_valid() {
		return @is_dir(PATH_TO_ROOT_WRITE) && is_writable(PATH_TO_ROOT_WRITE) && @file_get_contents(PATH_TO_ROOT."index.php") == @file_get_contents(PATH_TO_ROOT_WRITE."index.php");
	}
	static function admin_data_writable() {
		return @is_dir("./data") && @is_writable("./data/packagedata") && @is_writable("./data/serverfiledata") && @is_writable("./data/localfiledata");
	}
	static function directories_exist() {
		foreach (test::directories() as $id => $dir) {
			if (!$dir["exists"]) return false;
		}
		return true;
	}
	static function directories_writable() {
		foreach (test::directories() as $id => $dir) {
			if ($dir["write_required"] && !$dir["writable"]) return false;
		}
		return true;
	}
	static function directories_updatable() {
		foreach (test::directories() as $id => $dir) {
			if (!$dir["updatable"]) return false;
		}
		return true;
	}
	static function directories() {
		if (!config::config_exists()) return array();
		$return = array();
		foreach (config::search('/^folder_/') as $id => $path) {
			$id = substr($id, 7);
			$return[$id] = array("path" => $path);
			$return[$id]["write_required"] = ($id == 'data' || $id == 'compiled' || $id == 'cached' || $id == 'dcss_compiled' || $id == 'form_compiled');
			$return[$id]["exists"] = file::get_folder_path($id) !== false && @is_dir(get_file($id, '')->get_path());
			$return[$id]["writable"] = @is_writable(get_file($id, '')->get_path());
			$return[$id]["updatable"] = @is_writable(get_file($id, '')->get_write_path());
		}
		return $return;
	}
	static function dbconnections() {
		if (!config::config_exists()) return array();
		return config::get('db');
	}
}

