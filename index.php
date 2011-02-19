<?php
if (!defined('__DIR__')) { 
    $iPos = strrpos(__FILE__, "/"); 
    define("__DIR__", substr(__FILE__, 0, $iPos) . "/"); 
}

define('DS', DIRECTORY_SEPARATOR);
define('ROOT_BACK', __DIR__.DS);
define('DOMAIN', $_SERVER['HTTP_HOST']);

require_once ROOT_BACK.'models'.DS.'I.php';
require_once ROOT_BACK.CONTROLLERS_DIRECTORY.DS.'main.php';
?>