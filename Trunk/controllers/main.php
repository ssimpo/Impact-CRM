<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *	Main controller for the Impact platform.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 */

require_once ROOT_BACK.INCLUDES_DIRECTORY.DS.'adodb'.DS.'adodb.inc.php';
require_once('autoload.php');

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
    
    echo $tparser->parse(VIEWS_DIRECTORY.DS.USE_THEME.DS.'xml'.DS.'main.xml');
}

?>