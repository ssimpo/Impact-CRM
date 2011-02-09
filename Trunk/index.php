<?php
require_once('models/class.I.php');
define('ROOT_BACK',I::get_include_directory());
define('DOMAIN',$_SERVER['HTTP_HOST']);	
require_once(ROOT_BACK.'/controllers/main.php');
?>