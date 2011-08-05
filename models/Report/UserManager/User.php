<?php
defined('DIRECT_ACCESS_CHECK') or die;

/*
 *	Report_UserManager_User Class
 *
 *	User class to handle and individual use and their sessions.
 *	
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.2
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Report
 */
class Report_UserManager_User extends Base {
	private $onNewSession;
	private $onEndSession;
	private $start;
	private $sessionId ;
	private $sessionCount;
	private $sessionStart;
	private $sessionTime;
	private $sessionUri;
	private $count;
	
	
	public function __construct($onNewSession,$onEndSession) {
		$this->_init($onNewSession,$onEndSession);
	}
	
	/**
	 *	Initialize the object.
	 *
	 *	@private
	 */
	private function _init($onNewSession,$onEndSession) {
		$this->onNewSession = $onNewSession;
		$this->onEndSession = $onEndSession;
		$this->start = false;
		$this->sessionId = null;
		$this->count = 0;
		$this->sessionCount = 0;
		$this->sessionStart = false;
		$this->sessionTime = false;
		$this->sessionUri = null;
	}
	
	/**
	 *	Reset the current object.
	 *
	 *	Close session and detach all events, and re-intialize object.
	 *
	 *	@public
	 */
	public function reset() {
		$this->_init();
	}
	
	public function __get($property) {
		$convertedProperty = I::camelize($property);
		switch($convertedProperty) {
			case 'uri': return $this->sessionUri;
			case 'count': return $this->sessionCount;
			case 'sessionCount': return $this->count;
			case 'sessionId': return $this->sessionId;
			case 'period': return abs($this->sessionTime-$this->sessionStart);
		}
	}
	
	/**
	 *	Parse given data to update/create session
	 *
	 *	@private
	 *	@param array() $data The data array (usually from a parsed logline).
	 */
	public function parse($data) {
		if ($this->start === false) {
			$this->_handle_first_request($data);
			return;
		}
		
		$now = $this->_get_now($data);
		if (($this->sessionTime+(30*60)) < $now) {
			$this->_on_end_session();
			$this->_create_new_session($data);
		} else {
			$this->sessionTime = $now;
			$this->sessionUri = $this->_get_uri($data);
			$this->sessionCount++;
		}
	}
	
	/**
	 *	Intialize user information given by first request.
	 *
	 *	@private
	 *	@param array() $data The data array (usually from a parsed logline).
	 */
	private function _handle_first_request($data) {
		$now = $this->_get_now($data);
		$this->start = $now;
		$this->_create_new_session($data);
	}
	
	/**
	 *	Create a new session.
	 *
	 *	@private
	 *	@param array() $data The data array (usually from a parsed logline).
	 */
	private function _create_new_session($data) {
		$this->count++;
		$this->sessionId = $this->_get_hash(microtime());
		$this->sessionStart = $this->_get_now($data);
		$this->sessionTime = $this->sessionStart;
		$this->sessionUri = $this->_get_uri($data);
		$this->sessionCount = 1;
		$this->_on_new_session();
	}
	
	/**
	 *	Get the Calendar_DateTime for the supplied data.
	 *
	 *	@private
	 *	@param array() $data The data array (usually from a parsed logline).
	 *	@return Calendar_DateTime
	 */
	private function _get_now($data) {
		if (isset($data['datetime'])) {
			if ($data['datetime'] instanceof Calendar_DateTime) {
				return $data['datetime']->epoc;
			} elseif (is_int($data['datetime'])) {
				return $data['datetime'];
			} elseif (is_string($data['datetime'])) {
				$dateparser = new DateParser();
				return $dateparser->parse($parsed['datetime']);
			}
		} else {
			return time();
		}
	}
	
	/**
	 *	onNewSession Event
	 *
	 *	Call all the attached event for onNewSession.
	 *
	 *	@private
	 */
	private function _on_new_session() {
		if (is_array($this->onNewSession)) {
			$this->_call_user_func_array(
				$this->onNewSession[0],$this->onNewSession[1],$this
			);
		}
	}
	
	/**
	 *	onEndSession Event
	 *
	 *	Call all the attached event for onEndSession.
	 *
	 *	@private
	 */
	private function _on_end_session() {
		if (($this->start !== false) && (is_array($this->onEndSession))) {
			$this->_call_user_func_array(
				$this->onEndSession[0],$this->onEndSession[1],$this
			);
		}
	}
	
	/**
	 *	Generate the original URI from the supplied data.
	 *
	 *	@private
	 *	@param array() $data The data to use.
	 *	@return Filesystem_Path The URI.
	 */
	private function _get_uri($data) {
		$url = $data['domain'].$data['request'];
		$count = preg_match('/\A(.*?)(?:\/|\Z)/',$data['protocol'],$matches);
		if ($count > 0) {
			$url = $matches[1].'://'.$url;
		}
		$url = new Filesystem_Path(strtolower($url));
		
		return $url;
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
	 *	Get an 32-byte hash for a given data item.
	 *
	 *	@private
	 *	@param array()|string $data The data to get the item from
	 *	@param string $itemName The name of the item to use.  If non given then $data is a assumed to be a string, from which, a hash is generated.
	 *	@return string*32 The 32-byte string.
	 */
	private function _get_hash($data,$itemName='') {
		if ($itemName != '') {
			return md5((string) $data[$itemName]);
		} else {
			return md5((string) $data);
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
	
	/**
	 *	Destructor
	 *
	 *	Ensures that session is closed when the object is destroyed. Closing
	 *	session will call the onEndSession event.
	 */
	public function __destruct() {
		$this->_on_end_session();
	}
}
?>