<?php
defined('DIRECT_ACCESS_CHECK') or die;

/*
 *	Report Class
 *
 *	Suite of classes for producing Log reports
 *	
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.2
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Report
 */
class Report extends Base implements Iterator {
	private $suit;
	private $methods;
	
	public function __construct($type='') {
        $this->_init($type);
    }
	
	private function _init($type) {
		if ($type == '') {
			$this->suit = false;
			$this->methods = false;
		} else {
			$this->set_type($type);
		}
	}
	
	public function reset($type='') {
		$this->_init($type);
	}
	
	/**
	 *	Call a method within the the report suit.
	 *
	 *	This allows public methods within the suit to be reflected into this
	 *	object instance.  Using this methodology, the suit is never accessed
	 *	directly but through the report object, which reflects a range
	 *	of methods depending on what is being parsed.
	 *
	 *	@public
	 *	@param string $name The name of the suit method to call.
	 *	@param array() $arguments The parameters to send to the suit method.
	 *	@return mixed The results of executing the method.
	 */
	public function __call($name,$arguments) {
		if (array_key_exists($name,$this->methods)) {
			return $this->_call_user_func_array($this->suit,$name,$arguments);
		} else {
			throw new Exception('Undefined method "'.$name.'"');
		}
	}
	
	public function current() { return $this->suit->current(); }
	public function key() { return $this->suit->key(); }
	public function next() { return $this->suit->next(); }
	public function rewind() { return $this->suit->rewind(); }
	public function valid() { return $this->suit->valid(); }
	
	/**
	 *	Set the type of this report.
	 *
	 *	The type is the report suit to load.  Different data needs to be
	 *	processed in a different manor.  Access logs need some form of user
	 *	and session logging; hence the suit handles that for each section
	 *	of the report.
	 *
	 *	@public
	 *	@param string $type The report type.
	 *	@return boolean Did the report-suit load?
	 */
	public function set_type($type) {
		$type = strtolower('report_'.$type);
		$this->suit = $this->factory($type);
		if ((!is_object($this->suit)) || ($type != strtolower(get_class($this->suit)))) {
			return false;
		}
		
		$this->methods = array_flip(get_class_methods($this->suit));
		
		return true;
	}
	
	/**
	 *	Replacement for the PHP function call_user_func_array().
	 *
	 *	The internal function call_user_func_array, can be quite slow, this is
	 *	a much faster method if the number of arguments is low.  Assumes that
	 *	function being called is executed within the scope of an object.
	 *
	 *	@private
	 *	@param object{} $object The object to call a method within.
	 *	@param string $name The name of the method to call.
	 *	@param array() $arguments The parameters to send to the method.
	 *	@return mixed The result of the method.
	 */
	private function _call_user_func_array($object,$name,$arguments) {
		$arguments = $this->_convert_to_arguments_array($arguments);
		
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
}
?>