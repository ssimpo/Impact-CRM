<?php
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
 *	
 */

class Plugin {

    /**
    *		Factory method for plugins.
    *
    *		Simple method, which returns the required plugin from the plugins
    *		folder.  Plugin can then be run to generate content.
    *
    *		@access public
    *		@param string $type The plugin name to load.
    *		@return object/boolean The plugin class or false if it couldn't be found.
    */
    public static function factory ($type){
	if (include_once 'plugins/content/'.strtolower($type).'.php') {
	    $classname = 'Plugin_' . $type;
	    return new $classname;
	} else {
	    return false;
	}
    }
}
?>