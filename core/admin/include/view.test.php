<?php

if (LOGGED !== true) die();

print '<style>
		table.tests {
			width: 100%;
			margin: 5px;
			padding: 5px;
		}
		table.tests td {
			vertical-align: middle;
			border-bottom: solid 1px #CCCCCC;
		}
		table.tests td.title {
			width: 300px;
		}
	</style>';
print '
<h2>Test server configuration</h2>
This page gives you information about the server based on current configuration. Make sure you have no values in red otherwise the framework might not work properly. Values in orange might need your attention, but will not prevent the framework from working.';

print '<h3>Web server</h3>
	<table cellspacing="0" cellpadding="5" class="tests"><tr>
		<td class="title"><b>Running on Apache</b> (Recommended but not required)</td><td>'.(test::get_apache_version()? test::ok()." (".test::get_apache_version().")":test::warning("No")).'</td>
	</tr><tr>
		<td class="title"><b>PHP Version</b> (Required 5.0+, recommended 5.2+)</td><td>'.(test::get_php_version() >= 5.0? (test::get_php_version() >= 5.2? test::ok() : test::warning("OK")) : test::invalid("No")).' ('.test::get_php_version().')&nbsp;&nbsp;&nbsp;&nbsp;<a href="'.common::url('self').'?view=phpinfo">View PHP Info</a></td>
	</tr><tr>
		<td class="title"><b>Sessions</b> (Required)</td><td>'.(test::session_enabled()? test::ok() : test::invalid()).'</td>
	</tr><tr>
		<td class="title"><b>Magic quotes</b> (Recommended to be off)</td><td>'.(test::magic_quotes_on()? test::warning("Turn off for better performance") : test::ok("OFF")).'</td>
	</tr></table>

	<h3>Admin console and config file</h3>
	<table cellspacing="0" cellpadding="5" class="tests"><tr>
		<td class="title"><b>Valid path to root</b></td><td>'.(test::root_path_valid()? test::ok("YES") : test::invalid("NO")).' (Path: <i>'.PATH_TO_ROOT.'</i>)</td>
	</tr><tr>
		<td class="title"><b>Valid path to root for editing</b></td><td>'.(test::root_path_write_valid()? test::ok("YES") : test::invalid("NO. Update will not work if it is not valid")).' (Path: <i>'.PATH_TO_ROOT_WRITE.'</i>)</td>
	</tr><tr>
		<td class="title"><b>Config file exists</b></td><td>'.(test::config_exists()? test::ok("YES") : test::invalid("NO")).' (Path: <i>'.CONFIG_FILE.'</i>)</td>
	</tr><tr>
		<td class="title"><b>Config file is writable</b> (though edit path)</td><td>'.(test::config_writable()? test::ok("YES") : test::invalid("NO")).' (Path: <i>'.CONFIG_FILE_WRITE.'</i>)</td>
	</tr><tr>
		<td class="title"><b>Config is complete</b></td><td>'.(test::config_complete()? test::ok("YES") : test::invalid("NO. Run configuration again")).'</td>
	</tr></table>

	<h3>Directories</h3>
	<table cellspacing="0" cellpadding="5" class="tests"><tr>';
	
foreach (test::directories() as $id => $data) {
	print '
	<tr>
		<td class="title"><b>Folder: '.$id.'</b>'.($data['write_required']? ' (Needs to be writable)':'').'</td>
		<td>'.($data['exists']? ($data['write_required'] && !$data['writable']? test::invalid("Not writable"):test::ok("OK")) : test::invalid("NOT FOUND")).' (Path: <i>'.$data['path'].'</i>)</td>
		<td style="width: 90px; text-align:right;">'.($data['updatable']? test::ok("Updatable") : test::invalid("Not Updatable")).'</td>
	</tr>';
}

print '</table>
<h3>Loading framework</h3>';
if (!test::framework_working()) {
	print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;There are issues with current server configuration. Read above for more info. You can test framework and database connections once all requirements are fulfilled.';
} else {
	require(get_file("framework", "framework.php")->get_path());

	print '&nbsp;&nbsp;&nbsp;&nbsp;If you see no error above, then include was '.test::ok("successful").'
	<h3>Initializing framework</h3>';
	BF::init();
	print '&nbsp;&nbsp;&nbsp;&nbsp;If you see no error above, then initialization was '.test::ok("successful").'
	<h3>Database connexions</h3>';
	
	if (count(test::dbconnections()) > 0) {
		print '<table cellspacing="0" cellpadding="5" class="tests"><tr>';
		foreach (test::dbconnections() as $id => $data) {
			$error = false;
			try {
				BF::load_module("database");
				BF::load_module("database.".$data[0]);
				$class = "BF_DB_".$data[0];
				if (!call_user_func(array($class, "supported"))) throw new exception($data[0]." is not supported by your PHP server");
				$db = BF::gdb($id);
			} catch (exception $e) {
				$error = $e->getMessage();
			}
		
			print '
			<tr>
				<td class="title"><b>ID: '.$id.'</b>&nbsp;&nbsp;&nbsp;('.implode(", ", $data).')</td>
				<td>'.($error? test::invalid($error) : test::ok()).'</td>
			</tr>';
		}
		print '</table>';
	} else {
		print '&nbsp;&nbsp;&nbsp;&nbsp;'.test::ok("None");
	}
}
