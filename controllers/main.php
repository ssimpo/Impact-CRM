<?php
require_once(ROOT_BACK.'/models/class.Impact.php');

$impact = Impact::singleton();


if ($impact->pageErrorCheck) {
	$tparser = $impact->factory('templater');
	$tparser->init($impact->getPage(),$impact->application);
	
	echo $tparser->parse(ROOT_BACK.'/views/'.USE_THEME.'/main.xml');
}

?>