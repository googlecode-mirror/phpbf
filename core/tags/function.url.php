<?php
/*
 * Smarty plugin : url function
 * -------------------------------------------------------------
 * File:     function.url.php
 * Type:     function
 * Name:     url
 * Usage:    {url href='...' locale='en'}
 * Purpose:  Generate an url (no html) 
 * -------------------------------------------------------------
 */


function smarty_function_url($p, Smarty_Internal_Template $template)
{
	return $template->smarty->make_url(isset($p["href"])? $p["href"]:false, isset($p["locale"])? $p["locale"]:null);
}

?>
