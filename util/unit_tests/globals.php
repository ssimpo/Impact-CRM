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
if (!defined('MODELS_DIRECTORY')) {
    define('MODELS_DIRECTORY','models');
}
if (!defined('DIRECT_ACCESS_CHECK')) {
    define('DIRECT_ACCESS_CHECK',false);
}
if (!defined('USE_LOCAL_MODELS')) {
    define('USE_LOCAL_MODELS',false);
}
if (!defined('INCLUDES_DIRECTORY')) {
    define('INCLUDES_DIRECTORY','includes');
}

date_default_timezone_set('GMT');

require_once(ROOT_BACK.'Util'.DS.'unit_tests'.DS.'ImpactPHPUnit.php');
?>