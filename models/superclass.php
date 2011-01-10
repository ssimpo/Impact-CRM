<?php
/**
 *	Main base class.
 *
 *	Class is used as a base for most of the classes throughout the platform.
 *	It includes all the functions that are necessary to work
 *	using the, "Impact formula".
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Impact	
 */
class Impact_Superclass {
	
	/**
	 *	Factory method
	 *
	 *	The factory method is used to generate new classes according to the
	 *	standard rules of the Impact Platform.  The class is sometimes overidden
	 *	in base-classes if the class has it's own sub-classes that are loaded
	 *	on-the-fly.  Classes are only included if they are used,
	 *	saving load over-heads.  The factory method knows where to find the files.
	 *
	 *	@public
	 *	@static
	 *	@param String $className The name of the class to load.
	 *	@todo Method needs to be made more generic so it isn't overidden in subclasses
	*/
	public static function factory($className) {
		$dir = self::get_include_directory();
		
		if (include_once '/class.'.str_replace('_','.',$className).'.php') {
			return new $className;
		} else {
			throw new Exception($className.' Class not found');
		}
	}
	
	/**
	*	Get the the file location of the current class.
	*
	*	@public
	*/
	public function get_include_directory() {
		$debug = debug_backtrace();
		return dirname($debug[0][file]);
	}
	
	public function _add_square_brakets($txt) {
		$txt = '['.str_replace(',','],[',$txt).']';
		$txt = str_replace('[[','[',$txt);
		$txt = str_replace(']]',']',$txt);
		$txt = str_replace('[ ','[',$txt);
		return str_replace(' ]',']',$txt);
	}
}