<?php #Main site-wide Controller
define('ROOT_BACK','../../../');					#Root back to root directory of this installation

require_once(ROOT_BACK.'components/main/controller_core_include.php');

#Load the model and view
if (isset($_GET[scroller])) {
	$scrollerID = decode_and_slash($_GET[scroller]);
	$slides = array();
	
	require_once('models/loader.php');
	echo $JSON;
}	else {
	echo '{items:[""]}';
}
?>