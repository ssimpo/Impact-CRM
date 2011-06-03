<?php
/**
 *	Main controller for the Impact platform.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 */
function __autoload($className) {
    $classFileName = str_replace('_',DS,$className).'.php';
    if (!include_once ROOT_BACK.MODELS_DIRECTORY.DS.$classFileName) {
	if (I::contains($classFileName,'Base.php')) {
		$classFileName = str_replace(
			DS.'Base.php',
			'.php',
			$classFileName
		);
		if (!include_once ROOT_BACK.MODELS_DIRECTORY.DS.$classFileName) {
			throw new Exception($className.' Class not found');
		}
	}
    }
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
    
    $application->mainMenu = $database->get_menu('MAIN',1);
    $tparser = $application->factory('Templater');
    $tparser->init($database->get_page(),$application->settings);

    echo $tparser->parse(VIEWS_DIRECTORY.DS.USE_THEME.DS.'main.xml');
}

?>