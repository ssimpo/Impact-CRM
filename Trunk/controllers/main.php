<?php
/**
 *	Main controller for the Impact platform.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL	
 */

//Include the core/base classes, needed throughout
require_once(ROOT_BACK.'/models/base.Impact.php');
require_once(ROOT_BACK.'/models/class.Application.php');
require_once(ROOT_BACK.'/models/class.Database.php');

//Load the main application class and database class
$application = Application::singleton();
$application->setup();
$database = Database::singleton();


/**
 *	Check that the page/item being requested is valid,
 *	then load page, pass through the template parser and pass to the selected view
 */
if ($application->pageErrorCheck) {
	$tparser = $application->factory('templater');
	$tparser->init($database->getPage(),$application->settings);
	
	echo $tparser->parse(ROOT_BACK.'/views/'.USE_THEME.'/main.xml');
}

?>