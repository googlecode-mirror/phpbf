<?php

if (LOGGED !== true) die();

print '
<h2>Backup</h2>
To download a complete backup of your website root, your server must be running on a linux OS and PHP must be allowed to execute shell commands.
<br/><br/>
<a href="'.common::url('self').'?edit=backup">Download backup</a>.
';
