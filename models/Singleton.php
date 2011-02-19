<?php
/**
 *	Singleton Class
 *	
 * 	Must of the code for this class is taken directly from
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
	static private $instances = array();
	
	final private function __construct() {
	}
	
	/**
	 * Static method for instantiating a singleton object.
	 *
	 * @return object
	 */
	final static public function instance() {
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
	final private function __clone() {
	}

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