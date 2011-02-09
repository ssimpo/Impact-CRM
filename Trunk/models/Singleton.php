<?php
/**
 *	Singleton Class
 *	
 * 	This implementation of the singleton pattern does not conform to the strong
 * 	definition given by the "Gang of Four." The __construct() method has not be
 * 	privatized so that a singleton pattern is capable of being achieved; however,
 * 	multiple instantiations are also possible. This allows the user more freedom
 * 	with this pattern.  Must of the code for this class is taken directly from
 *	the PHP ActiveRecord Project <http://www.phpactiverecord.org/>.
 *
 * 	@author Stephen Simpson <me@simpo.org>
 * 	@author Kien La <http://github.com/kla/>
 * 	@author Jacques Fuentes <jpfuentes2@gmail.com>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Impact
 */
abstract class Singleton extends ImpactBase {
	/**
	 * Array of cached singleton objects.
	 *
	 * @param array
	 */
	private static $instances = array();

	/**
	 * Static method for instantiating a singleton object.
	 *
	 * @return object
	 */
	final public static function instance() {
		$className = get_called_class();

		if (!isset(self::$instances[$className])) {
			self::$instances[$className] = new $className;
		}
		return self::$instances[$className];
	}

	/**
	 * Singleton objects should not be cloned.
	 *
	 * @return void
	 */
	final private function __clone() {}

	/**
	 * Similar to a get_called_class() for a child class to invoke.
	 *
	 * @return string
	 */
	final protected function get_called_class() {
		$backtrace = debug_backtrace();
		return get_class($backtrace[2]['object']);
	}
}
?>