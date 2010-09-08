<?php
require_once('includes/functions.php');
define('ROOT_BACK',get_include_directory());
require_all_once(ROOT_BACK.'/includes/*.php');
require_once(ROOT_BACK.'/controllers/main.php');
?>