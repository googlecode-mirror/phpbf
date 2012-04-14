<?php

if (LOGGED !== true) die();

print '<h2>PhpBF is licensed under the <a href="http://www.gnu.org/licenses/gpl.html" target="_blank">GNU Lesser General Public License v3</a></h2><br/><br/><pre>'.@file_get_contents(get_file('framework', 'LICENSE')->get_path()).'</pre>';
