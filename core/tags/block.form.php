<?php
/*
 * Smarty plugin : form block
 * -------------------------------------------------------------
 * File:     block.form.php
 * Type:     block
 * Name:     form
 * Usage:    {form href='/dir/file' locale='en' name='...'} .. {/form}
 * param:    string	href or action [optional] : file to point, see doc of BF::gr function for detail 
 * param:    string	locale [optional] : force a locale for href (or current page if href is false)
 * param:    string	id [optional] : ID of the form. If id is empty, will take name as id
 * param:    string	name [optional] : name of the form
 * param:    string	method [optional] : method to send form data : GET/POST (default : POST)
 * param:    string	enctype [optional] : data encoding for transmission (alias 'file' can be used for 'application/x-www-form-urlencoded')
 * param:    object	data [optional] : Form object, or id of form data file (ex. 'login' for login.frm)
 * 			any other attributes will be appended to the tag
 * Purpose:  Print a <form action="..." ...></form> block
 * -------------------------------------------------------------
 */

function smarty_block_form($p, $content, Smarty_Internal_Template $template, &$repeat = false)
{
	// only output on the closing tag
	if (!$repeat) {
		$smarty = $template->smarty;
		// look up form data
		BF::load_module('BF_form');
		
		$form = gform(isset($smarty->_tag_stack[0][1]['data'])? $smarty->_tag_stack[0][1]['data'] : null);

		if (!isset($p['method'])) $p['method'] = $form->method;
		if (!isset($p['id']) && isset($p['name'])) $p['id'] = $p['name'];
		if (!isset($p['enctype']) && $form->enctype) $p['enctype'] = $form->enctype;

		// if enctype is 'file' alias
		if (isset($p['enctype']) && $p['enctype'] == 'file') $p['enctype'] = "multipart/form-data";


		// set url
		$html = "<form".$smarty->attr('action', $smarty->make_url(isset($p["href"])? $p["href"]:false, isset($p["locale"])? $p["locale"]:null));
		foreach ($p as $key => $val) {
			if ($key == 'action' || $key == 'data') continue;
			$html .= $smarty->attr($key, $val);
		}

		return $html.">".$content."</form>";

	}

}

?>
