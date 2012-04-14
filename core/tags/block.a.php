<?php
/*
 * Smarty plugin : a (link) block
 * -------------------------------------------------------------
 * File:     block.a.php
 * Type:     block
 * Name:     a
 * Usage:    {a href='/dir/file' locale='en'} .. {/a}
 * param:    string	href [optional] : file to point, see doc of BF::gr function for detail 
 * param:    string	locale [optional] : force a locale for href (or current page if href is false)
 * Purpose:  Print a <a href="..."></a> block, see also {url} function
 * -------------------------------------------------------------
 */

function smarty_block_a($p, $content, Smarty_Internal_Template $template, &$repeat)
{
	// only output on the closing tag
	if (!$repeat){
		$url = $template->smarty->make_url(isset($p["href"])? $p["href"]:false, isset($p["locale"])? $p["locale"]:null);
		
		// print tag
		$html = "<a".$template->smarty->attr('href', $url);
		foreach ($p as $key => $val) {
			if ($key == 'href' || $key == 'locale') continue;
			$html .= $template->smarty->attr($key, $val);
		}
		return $html.">".$content."</a>";
	}
}

?>
