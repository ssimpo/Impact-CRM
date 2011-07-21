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
	private $sessionTime;
	private $sessionUri;
	private $count;
	
	
	public function __construct() {
		$this->_init($onNewSession,$onEndSession);
	}
	
	private function _init($onNewSession,$onEndSession) {
		$this->$onNewSession = $onNewSession;
		$this->$onEndSession = $onEndSession;
		$this->start = false;
		$this->sessionId = null;
		$this->count = 0;
		$this->sessionCount = 0;
		$this->sessionTime = false;
		$this->sessionUri = null;
	}
	
	public function reset() {
		$this->_init();
	}
	
	public function handle_request(&$data) {
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
	
	private function _handle_first_request(&$data) {
		$now = $this->_get_now($data);
		$this->start = $now;
		$this->_create_new_session($data);
	}
	
	private function _create_new_session(&$data) {
		$this->count++;
		$this->sessionId = $this->_get_hash(microtime());
		$this->sessionTime = $this->_get_now($data);
		$this->sessionUri = $this->_get_uri($data);
		$this->sessionCount = 1;
		$this->_on_new_session();
	}
	
	private function _get_now(&$data) {
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
	
	private function _on_new_session() {
		$this->_call_user_func_array(
			$this->onNewSession[0],$this->onNewSession[1],$this
		);
	}
	
	private function _on_end_session() {
		$this->_call_user_func_array(
			$this->onEndSession[0],$this->onEndSession[1],$this
		);
	}
	
	/**
	 *	Generate the original URI from the supplied data.
	 *
	 *	@private
	 *	@param array() $data The data to use.
	 *	@return Filesystem_Path The URI.
	 */
	private function _get_uri(&$data) {
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
			return md5($data[$itemName]);
		} else {
			return md5($data);
		}
	}
}
?>