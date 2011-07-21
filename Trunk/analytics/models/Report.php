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
class LogReport extends Base {
	private $callers;
	private $eventTypes = array('onNewSession','onEndSession','onNewUser');
	private $users;
	private $sessions;
	
	public $useGoogleAnalytics = false;
	
	public function __construct() {
		$this->_init();
	}
	
	private function _init() {
		$this->useGoogleAnalytics = false;
		$this->_init_callers();
		$this->users = array();
		$this->session = array();
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
	
	public function parse($data) {
		$user = '';
		if ($this->has_user($data)) {
			$user = $this->get_user($data);
		} else {
			$user = $this->_create_user($data);
		}
		
		$session = '';
		if ($this->has_session($data)) {
			$session = $this->get_session($data);
		} else {
			$session = $this->_create_session($data);
		}
		
		$now = $data['datetime']->epoc;
		if (($session['time']+(30*60)) < $now) {
			$this->_on_end_session($session['id']);
			$session = $this->_create_session($data);
		} else {
			$session['time'] = $now;
		}
	}
	
	public function has_session($sessionId) {
		return isset($this->sessions[$sessionId]);
		
		if (is_string($sessionId)) {
			return isset($this->users[$sessionId]);
		} elseif (is_array($sessionId)) {
			$userId = $this->_get_user_id($sessionId);
			$user = $this->get_user($userId);
			$sessionId = $user['session'];
			if ($sessionId != '' ) {
				return isset($this->sessions[$sessionId]);	
			} else {
				return false;
			}
		}
	}
	
	public function &get_session($sessionId) {
		if (is_string($sessionId)) {
			if ($this->has_session($sessionId)) {
				return $this->sessions[$sessionId];
			}
		} elseif (is_array($userId)) {
			$userId = $this->_get_session_id($sessionId);
			if ($this->has_session($sessionId)) {
				return $this->sessions[$sessionId];
			}
		}
		
		throw new Exception('Cannot not find the specified session.');
	}
	
	private function &_create_session(&$data) {
		$sessionId = $this->_get_session_id($data);
		if ($this->has_session($sessionId)) {
			return $this->get_session($sessionId);
		}
		
		$this->sessions[$sessionId] = $this->_create_session_array($sessionId);
		$this->sessions[$sessionId]['time'] = $data['datetime']->epoc;
		$user = $this->_create_user($data);
		$this->sessions[$sessionId]['user'] = $user['id'];
		$this->_on_new_session($sessionId);
		
		return $this->sessions[$userId];
	}
	
	private function _create_session_array($sessionId) {
		$user = array(
			'user' => '', 'session' => $sessionId
		);
		
		return $user;
	}
	
	/**
	 *	Is there a user in the system relating to the data supplied.
	 *
	 *	Assumes that a userId is given and will check that user is defined. If
	 *	an array is given then calculate the userId from that array and then
	 *	check if calculated user is defined.
	 *
	 *	@public
	 *	@param string|array() $userId The ID of the user to check or data to calculate a user from.
	 *	@return boolean
	 */
	public function has_user($userId) {
		if (is_string($userId)) {
			return isset($this->users[$userId]);
		} elseif (is_array($userId)) {
			$userId = $this->_get_user_id($userId);
			return isset($this->users[$userId]);
		}
	}
	
	/**
	 *	Get the specified user-data
	 *
	 *	Assumes that a userId is given and will check that user is defined and
	 *	return an array reference if it is. If an array is given then calculate
	 *	the userId from that array and then return it's data.
	 *
	 *	@public
	 *	@param string|array() $userId The ID of the user to get or data to calculate a user from.
	 *	@return array()
	 */
	public function &get_user($userId) {
		if (is_string($userId)) {
			if ($this->has_user($userId)) {
				return $this->users[$userId];
			}
		} elseif (is_array($userId)) {
			$userId = $this->_get_user_id($userId);
			if ($this->has_user($userId)) {
				return $this->users[$userId];
			}
		}
		
		throw new Exception('Cannot not find the specified user.');
	}
	
	private function &_create_user(&$data) {
		$userId = $this->_get_user_id($userId);
		if ($this->has_user($userId)) {
			return $this->get_user($userId);
		}
		
		$this->users[$userId] = $this->_create_user_array($userId);
		$session = $this->create_session($data);
		$this->users[$userId]['session'] = $session['id'];
		$this->_on_new_user($userId);
		
		return $this->users[$userId];
	}
	
	private function _create_user_array($userId) {
		$user = array(
			'user' => $userId, 'session' => ''
		);
		
		return $user;
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
	
	private function _on_new_session($sessionId) {
		foreach ($this->callers['onNewSession'] as $caller) {
			$this->_call_user_func_array($caller[0],$caller[1],$sessionId);
		}
	}
	
	private function _on_end_session($sessionId) {
		foreach ($this->callers['onEndSession'] as $caller) {
			$this->_call_user_func_array($caller[0],$caller[1],$sessionId);
		}
	}
	
	private function _on_new_user($userId) {
		foreach ($this->callers['onNewUser'] as $caller) {
			$this->_call_user_func_array($caller[0],$caller[1],$userId);
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
	
	private function _get_session_id(&$data) {
		$sessionId = '';
		if ($this->has_session($data)) {
			$session = $this->get_session($data);
			return $session['id'];
		} else {
			return $this->_get_hash(microtime());
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
		if ($unid === false) {
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
}
?>