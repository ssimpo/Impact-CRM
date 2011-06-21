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
	
	public function close() {
		if ($this->_is_resource()) {
			fclose($this->handle);
		}
	}
	
	protected function _is_resource() {
		$handleType = gettype($this->handle);
		return ($this->_is_equal($handleType,'resource'));
	}
	
	protected function _is_equal($string1,$string2) {
		return (strtolower(trim($string1)) == strtolower(trim($string2)));
	}
	
	protected function __destruct() {
		$this->close();
	}
	
}
?>