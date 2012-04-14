<?php
/*
 * Smarty plugin : css block
 * -------------------------------------------------------------
 * File:     block.css.php
 * Type:     block
 * Name:     css
 * Usage:    {css src='XX.css'} .. {/css}
 * param:    string	file [optional] : filename of the css file (in the css folder by default). Content is ignored if src is provided
 * Purpose:  Print a <style> block or a <link css> block  
 * -------------------------------------------------------------
 */

function smarty_block_css($p, $content, Smarty_Internal_Template $template, &$repeat)
{
	// only output on the closing tag
	if(!$repeat){
	    	if (isset($p["src"])) {
			$html = "<link ".$template->smarty->attr("href", BF::gr($p["src"], 'css')->url())." rel='stylesheet' type='text/css'";
			foreach ($p as $key => $val) {
				if ($key == 'src') continue;
				$html .= $template->smarty->attr($key, $val);
			}
			return $html." />";
	    		
	    		
	    	} elseif (isset($content)) {
			$html = "<style";
			foreach ($p as $key => $val) {
				$html .= $template->smarty->attr($key, $val);
			}
			return $html.">".$content."</style>";
		}
	}

}
?>
