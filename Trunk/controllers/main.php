<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *	Main controller for the Impact platform.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 */
function __autoload($className) {
	
	if (!class_exists($className)) {
		$paths = array(ROOT_BACK.MODELS_DIRECTORY.DS);
		if (USE_LOCAL_MODELS) {
			array_unshift($paths,SITE_FOLDER.MODELS_DIRECTORY.DS);
		}
		
		foreach ($paths as $path) {
			$classFileName = str_replace('_',DS,$className).'.php';
			
			if (is_file($path.$classFileName)) {
				require_once $path.$classFileName;
				return true;
			}
		}
	}
	
	$config = simplexml_load_file(ROOT_BACK.INCLUDES_DIRECTORY.DS.'includes.xml');
	foreach ($config->param as $param) {
		if (strtolower($param['name']) == strtolower($className)) {
			$path = ROOT_BACK.INCLUDES_DIRECTORY.DS.$param['value'];
			if (is_file($path)) {
				require_once $path;
				return true;
			}
		}
	}
	
	throw new Exception($className.' Class not found');
}


//Load the main application class and database class
require_once ROOT_BACK.INCLUDES_DIRECTORY.DS.'adodb'.DS.'adodb.inc.php';
$application = Application::instance();
$application->setup();
$database = Database::instance();



/**
 *	Check that the page/item being requested is valid,
 *	then load page, pass through the template parser and pass to the selected view
 */
if ($application->pageErrorCheck) {
    $tparser = $application->factory('Template');
	
    $tparser->init(
		$database->get_page(),
		$application->settings
	);
    
    echo $tparser->parse(VIEWS_DIRECTORY.DS.USE_THEME.DS.'xml'.DS.'main.xml');
}

?>