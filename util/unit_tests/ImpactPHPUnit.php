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
        spl_autoload_register('__autoload');
		
		if ($className == '') {
			$className = $this->_get_test_classname();
		} else {
			if (is_array($className)) {
				$args = $className($args);
			}
		}
		
		self::$class = $className;
		$this->instance = $this->_get_class_instance(self::$class,$args);
    }
	
	/**
	 *	Get an instance of the specified class.
	 *
	 *	Uses the Impact coding standards to derive whether class is singleton
	 *	or not, allowing access via normal *new* methodology
	 *	or using a static.
	 *
	 *	@private
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
	
	/**
	 *	Get the name of the class we are testing.
	 *
	 *	Impact coding standards say that a test class should be named,
	 *	Test_<Name of class we are testing>, hence the subject of the tests
	 *	can be derived by looking at the name of the calling class.
	 *
	 *	@private
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
	 *	@protected
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
	
	/**
	 *	Get the value of a property, even if only accessible through the magic __get()method.
	 *	
	 *	@private
	 *	@param string $propertyName The name of the property to search for.
	 *	@return mixed The property value.
	 */
	private function _get_property_value($propertyName) {
		if (property_exists($this->instance,$propertyName)) {
			$properties = get_object_vars($this->instance);
			if (array_key_exists($propertyName,$properties)) {
				return $properties[$propertyName];
			}		
			return $this->_get_private_property_value($propertyName);
		} else {
			return $this->_get_private_property_value($propertyName);
		}
	}
	
	/**
	 *	Get the value of a property, even if it is private or protectd.
	 *	
	 *	@private
	 *	@param string $propertyName The name of the property to search for.
	 *	@return mixed The property value.
	 */
	private function _get_private_property_value($propertyName) {
		$class = new ReflectionClass(self::$class);
		$proprties = $class->getProperties();
		foreach ($proprties as $property) {
			if ($property->getName() == $propertyName) {
				$property->setAccessible(true);
				return $property->getValue($this->instance);
			}
		}
		
		return $this->_get_magic_property_value($propertyName);
	}
	
	/**
	 *	Get the value of a property accessible through the magic __get() method.
	 *	
	 *	@private
	 *	@param string $propertyName The name of the property to search for.
	 *	@return mixed The property value.
	 */
	private function _get_magic_property_value($propertyName) {
		$class = new ReflectionClass(self::$class);
		$methods = $class->getMethods();
		foreach ($methods as $method) {
			if ($this->_is_equal($method->name,'__get')) {
				return $method->invokeArgs(
					$this->instance,
					array($propertyName)
				);
			}
		}
		
		throw new Exception('Property "'.$propertyName.'" does not exist');
	}
	
	public function getMethodReturn($args=array(),$functionName='') {
		if ($functionName == '') {
			$functionName = $this->_get_function_name();
		}
		$args = $this->_convert_to_arguments_array($args);
		
		$method = self::get_method($functionName);
		
		return $method->invokeArgs($this->instance,$args);
	}
	
	public function assertMethodReturn($expected,$args=array(),$functionName='') {
		return $this->assertEquals(
			$expected,
			$this->getMethodReturn($args,$functionName)
		);
	}
	
	public function assertMethodPropertySet($propertyName,$expected,$args=array(),$functionName='') {
		$this->getMethodReturn($args,$functionName);
		$propertyValue = $this->_get_property_value($propertyName);
		
		return $this->assertEquals($expected,$propertyValue);
	}
	
	public function assertMethodPropertyType($propertyName,$expected,$args=array(),$functionName='') {
		$result = $this->getMethodReturn($args,$functionName);
		$propertyValue = $this->_get_property_value($propertyName);
		
		if ($this->_is_equal($expected,gettype($propertyValue))) {
			return $this->assertTrue(true);
		} else {
			return $this->assertTrue(
				$this->_is_equal($expected,get_class($propertyValue))
			);
		}
	}
	
	public function assertMethodReturnType($type,$args=array(),$functionName='') {
		$result = $this->getMethodReturn($args,$functionName);
		$resultType = gettype($result);
		
		if ($this->_is_equal($resultType,$type)) {
			return $this->assertTrue(true);
		} elseif ($this->_is_equal($resultType,'object')) {
			return $this->assertTrue(
				$this->_is_equal($type,get_class($propertyValue))
			);
		}
	}
	
	public function assertMethodReturnTrue($args=array(),$functionName='') {
		return $this->assertTrue($this->getMethodReturn($args,$functionName));
	}
	
	public function assertMethodReturnFalse($args=array(),$functionName='') {
		return $this->assertFalse($this->getMethodReturn($args,$functionName));
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
	 *	@private
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
	 *	@private
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
	 *	@private
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
	 *	@private
	 *	@return string The function name.
	 */
	private function _get_calling_function_name() {
		$backtrace = debug_backtrace();
		$thisClassName = strtolower(get_class($this));
		
		foreach ($backtrace as $trace) {
			if (strtolower($trace['class']) == $thisClassName) {
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
	 *	@private
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