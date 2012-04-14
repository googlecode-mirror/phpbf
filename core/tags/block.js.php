<?php
/*
 * Smarty plugin : js block
 * -------------------------------------------------------------
 * File:     block.js.php
 * Type:     block
 * Name:     js
 * Usage:    {js src='XX.js'} .. {/js}
 * param:    string	file [optional] : filename of the javascript file (in the js folder by default). Content is ignored if src is provided
 * Purpose:  Print a <script> block  
 * -------------------------------------------------------------
 */

function smarty_block_js($p, $content, Smarty_Internal_Template $template, &$repeat = false)
{
	// only output on the closing tag
	if(!$repeat){
	    	if (isset($p["src"])) {
			return "<script language='javascript' src='".BF::gr($p['src'], 'js')->url()."' type='text/javascript'></script>";
	    	} elseif (isset($content)) {
			return "<script language='javascript' type='text/javascript'>//<![CDATA[\n".$content."\n//]]></script>";
		}
	}

}

?>
