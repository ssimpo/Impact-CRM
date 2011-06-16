<?php
if (!defined('DIRECT_ACCESS_CHECK')) {
	die('Direct access is not allowed');
}

/**
 *	Plugin loading class
 *
 *	This is a simple (but powerful) class for loading plugins from a set folder.
 *	Plugins are then run by calling their run method passing the required
 *	attributes to them.  Text or XML is then returned and parsed by the
 *	template parser.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Templator
 */
class Plugin {

    /**
    *		Factory method for plugins.
    *
    *		Simple method, which returns the required plugin from the plugins
    *		folder.  Plugin can then be run to generate content.
    *
    *		@public
    *		@param string $type The plugin name to load.
    *		@return object/boolean The plugin class or false if it couldn't be found.
    */
    public static function factory($type) {
		$paths = array(
			ROOT_BACK.PLUGINS_DIRECTORY.DS.strtolower($type).'.php',
			ROOT_BACK.PLUGINS_DIRECTORY.DS.strtolower($type).DS.'controllers'.DS.'controller.php'
		);
		if (USE_LOCAL_PLUGINS) {
			array_unshift(
				$paths,
				SITE_FOLDER.PLUGINS_DIRECTORY.DS.strtolower($type).'.php'
			);
			array_unshift(
				$paths,
				SITE_FOLDER.PLUGINS_DIRECTORY.DS.strtolower($type).DS.'controllers'.DS.'controller.php'
			);
		}
		
		foreach ($paths as $path) {
			$classname = 'Plugin_' . $type;
			
			if (@include_once $path) {
				return new $classname;
			}
		}
		
		return false;
    }
}

/**
 *      The interface for all plugins
 *      
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Plugin
 */
interface Impact_Plugin {
    /**
     *  The implementation method for the plugin
     *
     *  @public
     *  @param Array $attributes All the attributes to run plugin against.  In
     *                              the format: param1=var1 ...etc.
     */
    public function run($attributes);
}
?>