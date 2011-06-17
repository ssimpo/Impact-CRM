<?php
/**
 *	Unit Test Framework for Impact.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends PHPUnit_Framework_TestCase
 */
abstract class ImpactPHPUnit extends PHPUnit_Framework_TestCase {
	static protected $class;
	protected $instance;
	
    protected function init($classname,$arg='') {
        spl_autoload_register('self::__autoload');
		
		self::$class = $classname;
		if ($this->_is_singleton(self::$class)) {
			$this->instance = $classname::instance();
		} else {
			if ($arg == ''){
				$this->instance = new self::$class;
			} else {
				$this->instance = new self::$class($arg);
			}
		}
    }
    
    private function __autoload($className) {
		$classFileName = str_replace('_',DS,$className).'.php';
		if ($className == 'Facebook') {
			require_once ROOT_BACK.'includes'.DS.'facebook'.DS.strtolower($classFileName);
		} else {
			require_once ROOT_BACK.MODELS_DIRECTORY.DS.$classFileName;
		}
    }
    
    protected static function get_method($name) {
        $class = new ReflectionClass(self::$class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
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
}