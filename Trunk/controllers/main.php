<?php
//Include main modules
require_once(ROOT_BACK.'/models/superclass.php');
require_once(ROOT_BACK.'/models/class.Impact.php');
require_once(ROOT_BACK.'/models/class.Database.php');

$impact = Impact::singleton();
$impact->setup();
$database = Database::singleton();


if ($impact->pageErrorCheck) {
	$tparser = $impact->factory('templater');
	$tparser->init($database->getPage(),$impact->application);
	
	echo $tparser->parse(ROOT_BACK.'/views/'.USE_THEME.'/main.xml');
}

?>