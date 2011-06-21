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