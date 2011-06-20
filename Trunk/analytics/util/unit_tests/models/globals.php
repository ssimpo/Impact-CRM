<?php
/**
 *	Global constants and settings needed for the Unit Tests.
 *	
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 */

if (!defined('__DIR__')) {
    $iPos = strrpos(__FILE__, "/");
    define('__DIR__', substr(__FILE__, 0, $iPos) . '/');
}
if (!defined('DS')) {
    define('DS',DIRECTORY_SEPARATOR);
}
if (!defined('ROOT_BACK')) {
    define('ROOT_BACK',__DIR__.DS.'..'.DS.'..'.DS.'..'.DS.'..'.DS);
}

require_once(ROOT_BACK.'analytics'.DS.'Util'.DS.'unit_tests'.DS.'globals.php');
?>