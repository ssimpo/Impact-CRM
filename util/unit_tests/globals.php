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
defined('DS') or define('DS',DIRECTORY_SEPARATOR);
defined('MODELS_DIRECTORY') or define('MODELS_DIRECTORY','models');
defined('DIRECT_ACCESS_CHECK') or define('DIRECT_ACCESS_CHECK',false);
defined('USE_LOCAL_MODELS') or define('USE_LOCAL_MODELS',false);
defined('INCLUDES_DIRECTORY') or define('INCLUDES_DIRECTORY','includes');
defined('CONTROLLERS_DIRECTORY') or define('CONTROLLERS_DIRECTORY','controllers');

date_default_timezone_set('GMT');
require_once(ROOT_BACK.CONTROLLERS_DIRECTORY.DS.'autoload.php');
require_once(ROOT_BACK.'Util'.DS.'unit_tests'.DS.'ImpactPHPUnit.php');
?>