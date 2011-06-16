<?php
/**
 *	Setup all the globals for Impact
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 */
if (!defined('DIRECT_ACCESS_CHECK')) {
	die('Direct access is not allowed');
}

if (!defined('__DIR__')) { 
    $iPos = strrpos(__FILE__, "/"); 
    define("__DIR__", substr(__FILE__, 0, $iPos) . "/"); 
}

define('DS', DIRECTORY_SEPARATOR);
define('ROOT_BACK', __DIR__.DS);
define('DOMAIN', $_SERVER['HTTP_HOST']);
define('CONTROLLERS_DIRECTORY', 'controllers');
define('CONFIG_DIRECTORY', 'config');
define('SITES_FILE', 'sites.xml');

if (is_dir(ROOT_BACK.'sites'.DS.strtolower(str_replace('www.','',$_SERVER['HTTP_HOST'])))) {
    define(
        'SITE_FOLDER',
        ROOT_BACK.'sites'.DS.strtolower(str_replace('www.','',$_SERVER['HTTP_HOST'])).DS
    );
} else {
    define('SITE_FOLDER',ROOT_BACK.'sites'.DS.'default'.DS);
}
?>