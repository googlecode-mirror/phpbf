<?php

if (LOGGED !== true) die();

print '
<h2>Thank for using the phpBF Framework</h2>
To get started click on <a href="'.common::url('self').'?view=config">Configure</a>.
<br/><br/>
<b>Version:</b> '.common::get_version().'
<br/><br/>
<b>License</b>: <a href="'.common::url('self').'?view=license">GNU Lesser General Public License v3</a>
<br/><br/>
Please help by reporting any bugs you encounter, and <a href="http://loicminghetti.net/en/contact" target="_blank">contact me</a> if you need any help ;-)
<br/><br/>
Project website: <a href="'.common::url('website').'">'.common::url('website').'</a>
<br/>
Project on sourceforge.net: <a href="'.common::url('sourceforge').'">'.common::url('sourceforge').'</a>
';
