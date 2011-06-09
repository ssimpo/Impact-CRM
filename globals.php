<?php
/**
 *	Setup all the globals for Impact
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 */

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
?>