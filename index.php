<?php
require_once 'models'.DIRECTORY_SEPARATOR.'I.php';
define('ROOT_BACK',I::get_include_directory());
define('DOMAIN',$_SERVER['HTTP_HOST']);	
require_once CONTROLLERS_DIRECTORY.DIRECTORY_SEPARATOR.'main.php';
?>