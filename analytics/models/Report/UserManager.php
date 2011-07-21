<?php
defined('DIRECT_ACCESS_CHECK') or die;

/*
 *	Report_UserManager Class
 *
 *	User/Session Manager class, for log reporting.
 *	
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.2
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Report
 */
class Report_UserManager extends Base {
	private $callers;
	private $eventTypes = array('onNewSession','onEndSession','onNewUser');
	private $users;
	
	public $useGoogleAnalytics = false;
	
	public function __construct() {
		$this->_init();
	}
	
	private function _init() {
		$this->useGoogleAnalytics = false;
		$this->_init_callers();
		$this->users = array();
	}
	
	private function _init_callers() {
		$this->clear_all_events();
		foreach ($this->eventTypes as $type) {
			$this->callers[$type] = array();
		}
	}
	
	public function reset() {
		$this->_init();
	}

	public function parse(&$data) {
		$userId = $this->_get_user_id($data);
		if (!isset($this->users[$userId])) {
			$this->users[$userId] = new Report_UserManager_User(
				array($this,'on_new_session'),
				array($this,'on_end_session')
			);
			$this->on_new_user($this->users[$userId]);
		}
		
		$this->users[$userId]->parse($data);
	}
	
	/**
	 *	Attach an class-method to a particular event.
	 *
	 *	Attach an event, if the event is available for attaching and the
	 *	event is not already attached to by the specified caller.  Hence, it
	 *	will not allow the same object+method pair to be attached twice.  It
	 *	will allow different instance of the same class.
	 *
	 *	@public
	 *	@param string $type The event type to attach to.
	 *	@param object|array The object to use or an array containing object + method-name.
	 *	@param string The name of the method to call.
	 *	@return boolean Did it attach?
	 */
	public function attach_event($type,$caller,$method='') {
		$caller = $this->_create_caller($caller,$method);
		if (isset($this->callers[$type])) {
			$found = $this->_search_callers($type,$caller);
			if (empty($found)) {
				array_push($this->callers[$type],$caller);
				return true;
			}
		} else {
			throw new Exception('Unknown event type "'.$type.'"');
		}
		
		return false;
	}
	
	/**
	 *	Remove an attached event.
	 *
	 *	@public
	 *	@param string $type The event type to unattached from.
	 *	@param object|array The object to use or an array containing object + method-name.
	 *	@param string The name of the method to call.
	 *	@return boolean Did it unattached?
	 */
	public function remove_event($type,$caller,$method='') {
		if (isset($this->callers[$type])) {
			$found = $this->_search_callers($type,$caller,$method);
			for ($i = 0; $i < count($found); $i++) {
				unset($this->callers['onNewSession'][$found[$i]]);
			}
		} else {
			throw new Exception('Unknown event type "'.$type.'"');
		}
	}
	
	/**
	 *	Clear all attached events of a specified type.
	 *
	 *	@public
	 *	@param string $type The type of event to clear.
	 *	@return boolean Did it clear?
	 */
	public function clear_event($type) {
		if (isset($this->callers[$type])) {
			$this->callers[$type] = array();
		} else {
			throw new Exception('Unknown event type "'.$type.'"');
		}
	}
	
	/**
	 *	Clear all attached events.
	 *
	 *	@public
	 *	@return boolean Did it clear?
	 */
	public function clear_all_events() {
		$this->callers = array();
		return true;
	}
	
	/**
	 *	Search for a specified caller, attached to the specified event.
	 *
	 *	@public
	 *	@param string $type The event type to search.
	 *	@param object|array The object to use or an array containing object + method-name.
	 *	@param string The name of the method to call.
	 *	@return array() The found attached callers.
	 */
	private function _search_callers($type,$caller,$method='') {
		$caller = $this->_create_caller($caller,$method);
		$found = array();
		
		if (isset($this->callers[$type])) {
			foreach ($this->callers[$type] as $id => $callerArray) {
				array_push($found,$id);
			}
		} else {
			throw new Exception('Unknown event type "'.$type.'"');
		}
		
		return $found;
	}
	
	/**
	 *	Create a method calling array.
	 *
	 *	The standard PHP 'caller array' consists of array(<Object>,<Method>),
	 *	and can be used in call_user_func() and related functions.
	 *
	 *	@private
	 *	@param object|array $caller An object instance or a caller-array.
	 *	@param string $method The method-name to call.
	 *	@return array() The caller-array.
	 */
	private function _create_caller($caller,$method='') {
		if (($method == '') && (is_array($caller))) {
			return $caller;
		} elseif (($method != '') && (!is_array($caller))) {
			return array($caller,$method);
		}
		
		throw new Exception('Unable to create method calling array.');
	}
	
	public function on_new_session($user) {
		foreach ($this->callers['onNewSession'] as $caller) {
			$this->_call_user_func_array($caller[0],$caller[1],$user);
		}
	}
	
	public function on_end_session($user) {
		foreach ($this->callers['onEndSession'] as $caller) {
			$this->_call_user_func_array($caller[0],$caller[1],$user);
		}
	}
	
	public function on_new_user($user) {
		foreach ($this->callers['onNewUser'] as $caller) {
			$this->_call_user_func_array($caller[0],$caller[1],$user);
		}
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
	 *	Get an 32-byte hash for a given data item.
	 *
	 *	@private
	 *	@param array()|string $data The data to get the item from
	 *	@param string $itemName The name of the item to use.  If non given then $data is a assumed to be a string, from which, a hash is generated.
	 *	@return string*32 The 32-byte string.
	 */
	private function _get_hash($data,$itemName='') {
		if ($itemName != '') {
			return md5($data[$itemName]);
		} else {
			return md5($data);
		}
	}
	
	/**
	 *	Generate a unique ID for a user represented by the given data.
	 *
	 *	Will use the Google Analytics Cookie if one is found (and option to use
	 *	this cookie is set) or the the logged-in user or the IP+Agent if
	 *	other methods fail.
	 *
	 *	@todo Grabbing from logged-in user.
	 *	
	 *	@private
	 *	@param array() $data The data to grab a UNID from.
	 *	@return string*32
	 */
	private function _get_user_id(&$data) {
		$userId = false;
		
		if ($this->useGoogleAnalytics) {
			$userId = $this->_get_google_analytics_user_id($data);
		}
		if ($userId === false) {
			return $this->_get_hash($data['ip'].$data['agent']);
		}
    
		return $userId;
	}
	
	/**
	 *	Generate a unique ID from the google analytics cookie.
	 *
	 *	Will take the __utma value and convert it into a 32-byte hash.
	 *	
	 *	@private
	 *	@param array() $data The data to grab the google analytics ID from.
	 *	@return string*32|boolean The UNID or false if cookie not found.
	 */
	private function _get_google_analytics_user_id(&$data) {
		if (isset($data['cookie'])) {
			if (isset($data['cookie']['__utma'])) {
				return $this->_get_hash($data['cookie']['__utma']);
			}
		}
		
		return false;
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
?>