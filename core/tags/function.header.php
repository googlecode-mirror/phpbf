<?php
/*
 * Smarty plugin : header function
 * -------------------------------------------------------------
 * File:     function.header.php
 * Type:     function
 * Name:     header
 * Usage:    {header title='' keywords='' description=''}
 * param	string	file [optional default "header.tpl"]: filename of the header template 
 * param	string	title : Title of the page
 * param	string	keywords : List of keywords to put in meta, comma separated
 * param	string	description : Description of the page to put in meta tag
 * param	string	creator : Creator of the page to put in meta tag
 * param	string	subject : Subject of the page to put in meta tag
 * Purpose:  Set some general infos about the page and display an header template. 
 * -------------------------------------------------------------
 */


function smarty_function_header($p, Smarty_Internal_Template $template)
{
	$tpl = $template->smarty;
 	if (isset($p['title'])) $tpl->data['title'] = $p['title'];
 	if (isset($p['keywords'])) $tpl->data['keywords'] = $p['keywords'];
 	if (isset($p['description'])) $tpl->data['description'] = $p['description'];
 	if (isset($p['creator'])) $tpl->data['creator'] = $p['creator'];
 	if (isset($p['subject'])) $tpl->data['subject'] = $p['subject'];
 	
 	$vars = array();
	foreach ($p as $key => $val) {
		if ($key == 'title' || $key == 'file' || $key == 'keywords' || $key == 'description' || $key == 'creator' || $key == 'subject') continue;
		$vars[$key] = $val;
	}
	
	return $template->getSubTemplate (isset($p['file'])? $p['file']:"header.tpl", $template->cache_id, $template->compile_id, null, null,$vars, 0);
}

?>
