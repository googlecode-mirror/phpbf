<?php


function get_file($folder, $name) {
	return new file($folder, $name);
}
class file {
	private $folder, $name;
	public function __construct ($folder, $name) {
		$this->folder = $folder;
		$this->name = $name;
	}
	public function get_path() {
		$folder_path = file::get_folder_path($this->folder);
		if ($folder_path === false) common::error("Unknown folder ID: ".$this->folder);
		$relative = $this->name[0] != '/' && strpos($this->name, ':') === false;
		return ($relative? PATH_TO_ROOT:'').$folder_path.$this->name;
	}
	public function get_write_path() {
		$folder_path = file::get_folder_path($this->folder);
		if ($folder_path === false) common::error("Unknown folder ID: ".$this->folder);
		$relative = $path[0] != '/' && strpos($path, ':') === false;
		return ($relative? PATH_TO_ROOT_WRITE:'').$folder_path.$this->name;
	}
	public function exists() {
		return file_exists($this->get_path());
	}
	public function md5() {
		return md5($this->read());
	}
	public function write($content) {
		$this->mkdir();
		return file_put_contents($this->get_write_path(), $content);
	}
	public function mkdir() {
		return @mkdir(dirname($this->get_write_path()), 0777, true);
	}
	public function read() {
		return file_get_contents($this->get_path());
	}
	public function del() {
		return @unlink($this->get_write_path());
	}
	
	static public function get_folder_path($id) {
		if (!$id) return '';
		static $folders = null;
		if (!config::config_exists()) return false;
		if ($folders === null) {
			config::load_config();
			$folders = config::search('/^folder_/');
		}
		return isset($folders['folder_'.$id])? $folders['folder_'.$id] : false;
	}
}
