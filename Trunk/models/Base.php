<?php
defined('DIRECT_ACCESS_CHECK') or die;

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
abstract class Base {
	
	/**
	 *	Factory method
	 *
	 *	The factory method is used to generate new classes according to the
	 *	standard rules of the Impact Platform.  The class is sometimes overridden
	 *	in base-classes if the class has it's own sub-classes that are loaded
	 *	on-the-fly.  Classes are only included if they are used,
	 *	saving load over-heads.  The factory method knows where to find the files.
	 *
	 *	@public
	 *	@param String $className The name of the class to load.
	*/
	public function factory($className,$args=array()) {
		if (!(isset($this) && get_class($this) == __CLASS__)) {
			$this->_dynamic_class_include($className);
		} else {
			self::_dynamic_class_include($className);
		}
		
		try {
			if (count($args) > 0) {
				$reflection = new ReflectionClass($className);
				return $reflection->newInstanceArgs($args);
			}
		} catch (ErrorException $e) {
			throw new Exception($className.' Class not found');
		}
		
		if ($this->_is_singleton($className)) {
			return $className::instance();
		} else {
			return new $className;
		}
	}
	
	/**
	 *	Test whether a given class should be initiated as a Singleton or not.
	 *
	 *	@private
	 *	@param string $className Name of the class to test.
	 *	@return boolean
	 */
	private function _is_singleton($className) {
		$methods = get_class_methods($className);
		if (!is_array($methods)) {
			return false;
		}
		return ((!in_array('__construct',$methods)) && (in_array('instance',$methods)));
	}
	
	/**
	 *	Dynamically include the files needed for a given class.
	 *
	 *	@private
	 *	@param string $className Name of the class to include files for.
	 */
	private function _dynamic_class_include($className) {
		$paths = array(ROOT_BACK.MODELS_DIRECTORY.DS);
		if (USE_LOCAL_MODELS) {
			array_unshift($paths,SITE_FOLDER.MODELS_DIRECTORY.DS);
		}
		
		foreach ($paths as $path) {
			$classFileName = str_replace('_',DS,$className).'.php';
			
			if (!@include_once $path.$classFileName) {
				if (I::contains($classFileName,'Base.php')) {
					$classFileName = str_replace(DS.'Base.php','.php',$classFileName);
					if (@include_once $path.$classFileName) {
						return true;
					}
				}
			} else {
				return true;
			}
		}
		
		throw new Exception($className.' Class not found');
	}
}