<?php

if (LOGGED !== true) die();


$log = get_file("log", "errorlog.txt")->read();
$dom = new DOMDocument();
$dom->loadXML('<root>'.$log.'</root>');

print '
<h2>Error log (<a href="'.common::url('self').'?edit=clear_log">clear</a>)</h2>
<ol>';
foreach ($dom->childNodes->item(0)->childNodes as $error) {
	if (!($error instanceof DOMElement)) continue;
	print '<li>';
	foreach ($error->childNodes as $line) {
		if (!$line instanceof DOMElement) continue;
		if ($line->tagName == 'trace') {
			print 'Trace:<ul>';
			foreach ($line->childNodes as $item) {
				if (!$item instanceof DOMElement || trim($item->nodeValue) == '') continue;
				print '<li>'.$item->nodeValue.'</li>';
			}
			print '</ul><br/>';
		} else print $line->tagName .': <b>'.$line->nodeValue.'</b><br/>';
	}
	print '</li>';
}
print "</ol>";
