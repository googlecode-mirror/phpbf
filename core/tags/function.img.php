<?php
/*
 * Smarty plugin : img function
 * -------------------------------------------------------------
 * File:     function.img.php
 * Type:     function
 * Name:     img
 * Usage:    {img src='...' alt='' hover=''}
 * Purpose:  Generate an img html tag. 
 * -------------------------------------------------------------
 */


function smarty_function_img($p, Smarty_Internal_Template $template)
{
	// set border to 0 to prevent ugly looking border when image is in an anchor tag
	if (!isset($p["border"])) $p["border"] = '0';
	$src = BF::gr($p["src"], 'img');
	
	// if image has an hover src
	if (isset($p["hover"])) {
		$hover = BF::gr($p['hover'], 'img');
		
		$p["onmouseover"] = (isset($p["onmouseover"])? $p["onmouseover"].";" : "")."this.src=\"./".$hover->url()."\";";
		$p["onmouseout"] = (isset($p["onmouseout"])? $p["onmouseout"].";" : "")."this.src=\"./".$src->url()."\";";
	}
	$html = "<img".$template->smarty->attr('src', $src->url()).$template->smarty->attr('alt', isset($p["alt"])? $p["alt"] : null, isset($p["title"])? $p["title"]:null);
	foreach ($p as $key => $val) {
		if ($key == 'src' || $key == 'alt' || $key == 'hover') continue;
		$html .= $template->smarty->attr($key, $val);
	}
	return $html." />";	
}

?>
