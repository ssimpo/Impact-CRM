<?php
if (!defined('__DIR__')) { 
    $iPos = strrpos(__FILE__, "/"); 
    define("__DIR__", substr(__FILE__, 0, $iPos) . "/"); 
}

define('DS', DIRECTORY_SEPARATOR);
define('ROOT_BACK', __DIR__.DS);
define('DOMAIN', $_SERVER['HTTP_HOST']);

load_config(ROOT_BACK.'config'.DS.'settings.xml');
require_once ROOT_BACK.CONTROLLERS_DIRECTORY.DS.'main.php';


/**
 * Load a config file
 *
 * 	Loads a config file (XML) and returns it's values as an array. String-values
 * 	are returned as strings and integer-values as integers.
 *
 *	@param String $path Location of the settings file.
 *	@return string()|integer()
 *	@todo Make it work with more complex data types.
*/
function load_config($path) {
	$config = simplexml_load_file($path);
        
	foreach ($config->param as $param) {
		if (!defined($param['name'])) {
			switch ($param['type']) {
				case 'string':
					define($param['name'],$param['value']);
					break;
				case 'integer':
					define($param['name'],(int) $param['value']);
					break;
			}
		}
	}
}
?>