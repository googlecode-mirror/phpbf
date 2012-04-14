<?php

if (LOGGED !== true) die();

config::load_confdata();

$conf = array();

foreach (config::$sections as $id_sec => $sec) {
	foreach (config::$subsections[$id_sec] as $id_sub => $sub) {
		foreach (config::$fields[$id_sec][$id_sub] as $id => $field) {
			$value = $field["default"];
			if (isset($_POST[$id])) {
				$value = $_POST[$id];
				if (isset($field["cast"])) {
					switch ($field["cast"]) {
						case 'bool' : $value = 1 == (int)$value; break;
						case 'int' : $value = (int)$value; break;
						case 'array' : $value = array_values(array_filter(array_map("trim", explode("|", $value))));
					}
				}
			} elseif ($field['type'] == "checkbox" && isset($_POST[$id."_checkbox_submited"])) {
				$value = false;
			}
			if ($field["onsave"]) {
				$field["onsave"]($id, $value, $conf);
			} else {
				switch ($field['type']) {
					case 'radio' :
					case 'select' :
						foreach ($field['options'] as $option_value => $text) {
							if ($option_value == $value) $conf[$id] = $value;
						}
						break;
					case 'checkbox' :
					case 'table' :
					case 'custom' :
					case 'text' :
					default : 
						$conf[$id] = $value; break;
				}
			}
		}
	}
}
config::save_config($conf);
header("Location: http://" . $_SERVER['HTTP_HOST'].(dirname($_SERVER['PHP_SELF']) == "/"? "":$_SERVER['PHP_SELF'])."?view=config_saved");


