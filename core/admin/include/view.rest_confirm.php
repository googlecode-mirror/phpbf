<?php

if (LOGGED !== true) die();


print '
	<h2>Reset config file</h2>
	Are you sure you want to reset config file with default values?<br/>It is recommended to backup config file first.
	<br/><br/>
	<input type="button" value="Reset" onclick="document.location.href=\''.common::url('self').'?edit=config\';"/>&nbsp;
	<input type="button" value="Cancel" onclick="document.location.href=\''.common::url('self').'?view=config\';"/>
	';

