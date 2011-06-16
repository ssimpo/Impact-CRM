<?php
if (!defined('DIRECT_ACCESS_CHECK')) {
	die('Direct access is not allowed');
}

/**
 *	Main controller for the Impact platform.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 */
function __autoload($className) {
    
	$paths = array(ROOT_BACK.MODELS_DIRECTORY.DS);
	if (USE_LOCAL_MODELS) {
		array_unshift($paths,SITE_FOLDER.MODELS_DIRECTORY.DS);
	}
	
	foreach ($paths as $path) {
		$classFileName = str_replace('_',DS,$className).'.php';
		
		if (!@include_once $path.$classFileName) {
			$classFileName = str_replace(DS.'Base.php','.php',$classFileName);
			if (@include_once $path.$classFileName) {
				return true;
			}
		} else {
			return true;
		}
	}
	
	throw new Exception($className.' Class not found');
}


//Load the main application class and database class
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
    
    echo $tparser->parse(VIEWS_DIRECTORY.DS.USE_THEME.DS.'main.xml');
}

?>