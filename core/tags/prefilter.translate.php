<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     prefilter.translate.php
 * Type:     prefilter
 * Name:     translate
 * Purpose:  Tranlste templates containing [xx: tags
 * -------------------------------------------------------------
 */
 
function smarty_prefilter_translate($source, Smarty_Internal_Template $template)
{
	return BF::gl()->tl($source);
}


