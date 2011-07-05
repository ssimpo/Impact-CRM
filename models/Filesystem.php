<?php
defined('DIRECT_ACCESS_CHECK') or die;

/*
 *	Filesystem Base class
 *
 *	Core functionality used in filesystem classes.
 *	
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.6
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Filesystem
 */
abstract class Filesystem extends Base {
	protected $settings = array();
	protected $handle;
	
	const FSLASH = "/";
	const BSLASH = "\\";
	
	/**
	 *	Generic get property method.
	 *
	 *	Get the value of an application property.  Values are stored in
	 *	the application array and accessed via the __set and __get methods.
	 *
	 *	@public
	 */
	public function __get($property) {
		$convertedProperty = I::camelize($property);
		if (isset($this->settings[$convertedProperty])) {
			return $this->settings[$convertedProperty];
		} else {
			if ($property == 'settings') {
				return $this->settings;
			}
			throw new Exception('Property: '.$convertedProperty.', does not exist');
		}
	}
	
	/**
	 *	Replacement for the PHP function call_user_func_array().
	 *
	 *	The internal function call_user_func_array, can be quite slow, this is
	 *	a much faster method if the number of arguments is low.  Assumes that
	 *	function being called is executed within the scope of an object.
	 *
	 *	@protected
	 *	@param object{} $object The object to call a method within.
	 *	@param string $name The name of the method to call.
	 *	@param array() $arguments The parameters to send to the method.
	 *	@return mixed The result of the method.
	 */
	protected function _call_user_func_array($object,$name,$arguments) {
		switch (count($arguments)) {
			case 0: return $object->{$name}();
			case 1: return $object->{$name}($arguments[0]);
			case 2: return $object->{$name}($arguments[0],$arguments[1]);
			case 3: return $object->{$name}($arguments[0],$arguments[1],$arguments[2]);
			case 4: return $object->{$name}($arguments[0],$arguments[1],$arguments[2],$arguments[3]);
			default: return call_user_func_array(array($object,$name),$arguments);
		}
	}
	
	/**
	 *	Check whether the internal property 'handle' is pointing to resource.
	 *
	 *	@private
	 *	@return boolean
	 */
	protected function _is_resource() {
		$handleType = gettype($this->handle);
		return ($this->_is_equal($handleType,'resource'));
	}
	
	/**
	 *	Test whether two variable are equal
	 *
	 *	Are they equal when trimmed, lower-cased and converted to strings.
	 *
	 *	@param mixed $string1 The first variable to test.
	 *	@param mixed $string2 The second variable to test.
	 *	@return boolean
	 */
	protected function _is_equal($string1,$string2) {
		$testString1 = (string) $string1;
		$testString2 = (string) $string2;
		return (strtolower(trim($testString1)) == strtolower(trim($testString2)));
	}
	
	/**
	 *	Destructor.
	 *
	 *	Close any open handles.
	 *
	 *	@public
	 */
	public function __destruct() {
		$this->close();
	}
	
}
?>