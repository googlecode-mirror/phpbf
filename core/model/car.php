<?php

/**
* @file car.php
*/

BF::load_module('BF_record');

class car extends BF_record {
	static public $table = "car";
	static public $id_field = "id";
	static public $default_fields = array("id", "name", "price");
	static public $db = "database";
}

?>
