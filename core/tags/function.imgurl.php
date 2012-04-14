<?php
/*
 * Smarty plugin : imgurl function
 * -------------------------------------------------------------
 * File:     function.imgurl.php
 * Type:     function
 * Name:     imgurl
 * Usage:    {imgurl src='...'}
 * Purpose:  Generate url of an image 
 * -------------------------------------------------------------
 */


function smarty_function_url($p, Smarty_Internal_Template $template)
{
	return BF::gr($p["src"], 'img')->url();
}

?>
