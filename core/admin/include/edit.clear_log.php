<?php

if (LOGGED !== true) die();

get_file('data', 'errorlog.txt')->write('');
header("Location: http://" . $_SERVER['HTTP_HOST'].(dirname($_SERVER['PHP_SELF']) == "/"? "":$_SERVER['PHP_SELF'])."?view=log");

