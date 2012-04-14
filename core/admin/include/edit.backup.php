<?php

if (LOGGED !== true) die();

header('Content-Type: application/x-gzip');
$content_disp = ( ereg('MSIE ([0-9].[0-9]{1,2})', $HTTP_USER_AGENT) == 'IE') ? 'inline' : 'attachment';
header('Content-Disposition: ' . $content_disp . '; filename="backup.tar.gz"');
header('Pragma: no-cache');
header('Expires: 0');
passthru( "tar cz ".PATH_TO_ROOT);
die();
