<?php

if (LOGGED !== true) die();

print '
<h2>New configuration saved</h2>
To edit again, hit <a href="'.common::url('self').'?view=config">Configure</a>. The new content of <i>config.php</i> is below. To access this values from you scripts, read the Configuration section in the doc.
<br/><br/>
<pre>'.htmlentities(file_get_contents(CONFIG_FILE)).'</pre>';
