<?php
/*
 * Smarty plugin : footer function
 * -------------------------------------------------------------
 * File:     function.footer.php
 * Type:     function
 * Name:     footer
 * Usage:    {footer}
 * param	string	file [optional default "footer.tpl"]: filename of the footer template
 * Purpose:  Display a footer template. 
 * -------------------------------------------------------------
 */


function smarty_function_footer($p, Smarty_Internal_Template $template)
{
 	$vars = array();
	foreach ($p as $key => $val) {
		if ($key == 'file') continue;
		$vars[$key] = $val;
	}
	return $template->getSubTemplate (isset($p['file'])? $p['file']:"footer.tpl", $template->cache_id, $template->compile_id, null, null,$vars, 0);

}
	
?>
