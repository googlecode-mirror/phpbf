<?php

require_once('./core/lib/phpbf/framework.php');
BF::init();


BF::load_model("car");

$id = BF::gg(0);
$action = BF::gg(1);


if ($action == "view") {
	
	$car = new car($id);
	if (!$car->exists()) throw new BF_not_found();

	BF::load_module("BF_output_template");
	$tpl = new BF_output_template("view_car");
	$tpl->assign('car', $car);
	$tpl->disp();
	
} elseif ($action == "save") {

	BF::load_module("BF_form");
	$form = new BF_form('edit_car');
	
	$car = new car($id);
	if (!$car->exists()) throw new BF_not_found();	

	// check
	if (!$form->check()) $form->show_error();
	
	// process
	$car->name = $form->gval("name");
	$car->price = $form->gval("price");
	$car->save();
	
	// redirect
	BF::gr("example")->redirect();
	
} else {
	
	// list all cars from db
	$list = BF::glist('car');
	
	// display
	BF::load_module("BF_output_template");
	$tpl = new BF_output_template("list_cars");
	$tpl->assign('list', $list);
	$tpl->disp();
}




?>
