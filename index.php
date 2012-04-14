<?PHP


/*-----------------------------------------------------*\

                  YOUR WEBSITE HERE

\*-----------------------------------------------------*/


// This website uses phpBF


require_once('./core/lib/phpbf/framework.php');
BF::init();

$page = BF::gg(0);

if (!$page || $page == "index.php") {
	
	BF::gr(BF::gc('page_default'))->redirect();

// try loading a template
} elseif ( BF::gr("/tpl/".$page.'.tpl')->exists() ) {
	
	BF::load_module("BF_output_template");
	$tpl = new BF_output_template($page);
	$tpl->disp();
	
} else {
	throw new BF_not_found();
}


?>
