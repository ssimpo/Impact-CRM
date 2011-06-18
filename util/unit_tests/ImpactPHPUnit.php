<?php
/**
 *	Unit Test Framework for Impact.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.2
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends PHPUnit_Framework_TestCase
 */
abstract class ImpactPHPUnit extends PHPUnit_Framework_TestCase {
	static protected $class;
	protected $instance;
	
    protected function init($className='',$args=array()) {
        spl_autoload_register('self::__autoload');
		
		if ($className == '') {
			self::$class = $this->_get_test_classname();
		} else {
			self::$class = $className;
		}
		
		$this->instance = $this->_get_class_instance(self::$class);
    }
	
	/**
	 *	Get an instance of the specified class.
	 *
	 *	Uses the Impact coding standards to derive whether class is singleton
	 *	or not, allowing access via normal *new* methodology
	 *	or using a static.
	 *
	 *	@param string $className The name of the class to get.
	 *	@param array() $args The argument to invoke on class creation.
	 *	@return object An instance of the class.
	 */
	private function _get_class_instance($className,$args=array()) {
		if ($this->_is_singleton($className)) {
			return $className::instance();
		} else {
			if (!empty($args)) {
				if (!is_array($args)) {
					$args = array($args);
				}
				$reflection = new ReflectionClass($className);
				return $reflection->newInstanceArgs($args);
			} else {
				return new $className;
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
	
	/**
	 *	Get the name of the class we are testing.
	 *
	 *	Impact coding standards say that a test class should be named,
	 *	Test_<Name of class we are testing>, hence the subject of the tests
	 *	can be derived by looking at the name of the calling class.
	 *
	 *	@return string The name of the calling class.
	 */
	private function _get_test_classname() {
		$backtrace = debug_backtrace();
		$className = $backtrace[0]['class'];
		foreach ($backtrace as $trace) {
			if ($className != $trace['class']) {
				$className = preg_replace('/^Test_/','',$trace['class']);
				return $className;
			}
		}
		
		throw new Exception('Could not derive the class, which this set of tests is operating on.');
	}
    
	/**
	 *	Get a method from the current class and make it accessible.
	 *
	 *	@note Using the Reflection Class, access is given to private and protected arrays.
	 *
	 *	@param string $name The name of the method to provide.
	 *	@return ReflectionMethod The method as an object, which can be operated on.
	 */
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
	
	public function assertMethodReturn($expected,$args=array(),$functionName='') {
		if ($functionName == '') {
			$functionName = $this->_get_function_name();
		}
		$args = $this->_convert_to_arguments_array($args);
		
		$method = self::get_method($functionName);
		return $this->assertEquals(
			$expected,
			$method->invokeArgs($this->instance,$args)
		);
	}
	
	public function assertMethodPropertySet($proprtyName,$expected,$args=array(),$functionName='') {
		if ($functionName == '') {
			$functionName = $this->_get_function_name();
		}
		$args = $this->_convert_to_arguments_array($args);
		$method = self::get_method($functionName);
		$method->invokeArgs($this->instance,$args);
		
		$properties = array();
		if (property_exists($this->instance,$proprtyName)) {
			$properties = get_object_vars($this->instance);
		} else {
			$properties = $this->instance->settings;
		}
		
		if (!empty($properties)) {
			if (array_key_exists($proprtyName,$properties)) {
				return $this->assertEquals($expected,$properties[$proprtyName]);
			}
		}
		
		throw new Exception('Property "'.$proprtyName.'" does not exist');
	}
	
	public function assertMethodReturnTrue($args=array(),$functionName='') {
		if ($functionName == '') {
			$functionName = $this->_get_function_name();
		}
		$args = $this->_convert_to_arguments_array($args);
		
		$method = self::get_method($functionName);
		return $this->assertTrue($method->invokeArgs($this->instance,$args));
	}
	
	public function assertMethodReturnFalse($args=array(),$functionName='') {
		if ($functionName == '') {
			$functionName = $this->_get_function_name();
		}
		$args = $this->_convert_to_arguments_array($args);
		
		$method = self::get_method($functionName);
		return $this->assertFalse($method->invokeArgs($this->instance,$args));
	}
	
	/**
	 *	Turning an argument into the correct format for calling invokeArgs.
	 *
	 *	This method makes assert calling easier.  If a single argument is
	 *	presented it is wrapped-up in an array an invoked against the method
	 *	we are currently testing.  If an array is presented, the function
	 *	checks to see if it is an argument array and then invokes it.  If it
	 *	fails that test then it is again wrapped in an array and invoked.
	 *
	 *	@param mixed $args The argument(s) to convert.
	 *	@return array() An argument list.
	 */
	private function _convert_to_arguments_array($args) {
		if (!is_array($args)) {
			return array($args);
		} else {
			if ($this->_is_numeric_indexed_array($args)) {
				return $args;
			} else {
				return array($args);
			}
		}
	}
	
	/**
	 *	Get the function, which the current test is testing.
	 *
	 *	The principle here is that according to the Impact coding standards
	 *	each test will be called test_<Function it tests>(), so the subject
	 *	of each test can be derived from the test name.  Coding standards
	 *	dictate that private and protected methods start with an underscore, so
	 *	if a method is not found, it is assumed to be private.
	 *
	 *	@return String The name of the function.
	 */
	private function _get_function_name() {
		$testFunctionName = $this->_get_calling_function_name();
		$functionName = preg_replace('/^test_/','',$testFunctionName);
		
		if ($this->_check_function_name($functionName)) {
			return $functionName;
		}
		if ($this->_check_function_name('_'.$functionName)) {
			return '_'.$functionName;
		}
		
		throw new Exception($functionName.' Does not exist in the class "'.self::$class.'"');
	}
	
	/**
	 *	Check whether a method exists within a given class.
	 *
	 *	@param string $functionName The name of the function to test.
	 *	@return boolean
	 */
	private function _check_function_name($functionName) {
		
		if (substr($functionName,0,1) != '_') {
			$methods = get_class_methods(self::$class);
			if (in_array($functionName,$methods)) {
				return true;
			}
		}
		
		$class = new ReflectionClass(self::$class);
		$methods = $class->getMethods();
		foreach ($methods as $method) {
			if ($this->_is_equal($method->name,$functionName)) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 *	Get the name of the calling function (the caller outside this class).
	 *
	 *	@return string The function name.
	 */
	private function _get_calling_function_name() {
		$backtrace = debug_backtrace();
		
		foreach ($backtrace as $trace) {
			if (strtolower($trace['class']) == strtolower('Test_'.self::$class)) {
				return $trace['function'];
			}
		}
		
		throw new Exception('Cannot find the method-name this test is operating on.');
	}
	
	/**
	 *	Test whether two strings are identical after trimming, lowercasing.
	 *
	 *	@note Method will accept non-strings as long as they can be converted to a string.
	 *
	 *	@param mixed $string1 The first string to test.
	 *	@param mixed $string2 The second string to test.
	 *	@return boolean
	 */
	private function _is_equal($string1,$string2) {
		$string1 = (string) $string1;
		$string2 = (string) $string2;
		return (strtolower(trim($string1)) == strtolower(trim($string2)));
	}
	
	/**
	 *
	 *	Test if an array is indexed numerically.
	 *	
	 *	@private
	 *	@param Array() $array The array to test.
	 *	@return	Boolean
	 */
	private function _is_numeric_indexed_array($array) {
		$is_numeric_indexed = true;
		
		if (is_array($array)) {
			foreach ($array as $key => $value) {
				if (!is_numeric($key)) {
					$is_numeric_indexed = false;
				}
			}
		} else {
			throw new Exception('Expected parameter to be an array.');
		}
		
		return $is_numeric_indexed;
	}
}