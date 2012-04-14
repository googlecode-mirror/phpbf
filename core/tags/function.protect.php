<?php
/*
 * Smarty plugin : protect function
 * -------------------------------------------------------------
 * File:     function.protect.php
 * Type:     function
 * Name:     protect
 * Usage:    {protect access='g:admin'}
 * Param:    string	access : access restriction to check. '' (empty) for public.
 * Purpose:  Set an access restriction for this template.
 * -------------------------------------------------------------
 */

function smarty_function_protect($params, Smarty_Internal_Template $template)
{
 	if (isset($params['access'])) {
 		$template->smarty->data['access'] = $params['access'];
 		if (!BF::ga($template->smarty->data['access'])) throw new BF_forbidden();
 	}
}
	
?>
