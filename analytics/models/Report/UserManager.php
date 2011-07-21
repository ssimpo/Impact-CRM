<?php
defined('DIRECT_ACCESS_CHECK') or die;

/*
 *	Report_UserManager Class
 *
 *	User/Session Manager class, for log reporting.
 *	
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Report
 */
class Report_UserManager extends Base implements Iterator {
	private $sessions;
	private $position;
	private $order;
	private $log;
	private $lastLine = null;
	
	public $useGoogleAnalytics = false;
	
	public function __construct() {
		$this->_init();
	}
	
	private function _init() {
		$this->sessions = array();
		$this->users = array();
		$this->log = array();
		$this->useGoogleAnalytics = false;
		$this->position = -1;
		$this->order = null;
	}
	
	/**
	 *	Call all the initialization code again.
	 *
	 *	Method will effectively call all the initialization code again,
	 *	resetting all the internal arrays and logs.
	 *	
	 *	@public
	 */
	public function reset() {
		$this->_init();
	}
	
	public function next() {
		if ($this->position == -1) {
			$this->rewind();
		}
		
		$unid = $this->order[$this->position];
		$this->lastLine = $this->log[$unid];
		$this->position++;
		return $this->lastLine;
	}
	
	public function rewind() {
		foreach ($this->sessions as $userID => $session) {
			$this->_add_exit($userID);
			if ($session['count'] == 1) {
				$this->_add_bounce($userID);
			}
		}
		
		uasort($this->log, array($this,'_hits_sort'));
		
		$this->position = 0;
		$this->order = array_keys($this->log);
	}
	
	public function current() {
		return $this->lastLine;
	}
	
	public function valid() {
		if (($this->position > -1) && ($this->position < count($this->log))) {
			return true;
		}
		return false;
	}
	
	public function key() {
		return $this->position;
	}
	
	public function all() {
	}
	
	
	/**
	 *	Parse a set of data.
	 *
	 *	@public
	 *	@param array() $data The data to parse (usually equating to a parsed log-line).
	 */
	public function parse(&$data) {
		$uri = $this->_store_uri($data);
		
		if ($uri != '') {
			$uriRef = $this->_get_hash($data,'URI');
			$this->_add_hit($data,$uriRef);
			$this->_add_processing_time($data,$uriRef);
		
			$userRef = $this->_get_user_unid($data);
			$this->_add_user_hit($data,$userRef,$uriRef);
		
			$sessionID = $this->_handle_sessions($data,$userRef);
			$this->_add_session_hit($data);
		}
	}
	
	private function _hits_sort($a, $b) {
		if ($a['hits'] == $b['hits']) {
			if (count($a['sessions']) == count($b['sessions'])) {
				if (count($a['users']) == count($b['users'])) {
					return 0;
				}
				return (count($a['users']) > count($b['users'])) ? -1 : 1;
			}
			return (count($a['sessions']) > count($b['sessions'])) ? -1 : 1;
		}
		return ($a['hits'] > $b['hits']) ? -1 : 1;
	}
	
	/**
	 *	Add a user-hit for the given data.
	 *
	 *	@param array() $data The data to use for creating a hit.
	 *	@param string*32 $userID The internal UNID to use for the user
	 *	@param string*32 $unid The internal UNID to use for the URI
	 */
	private function _add_user_hit(&$data,$userID,$unid) {
		if (!isset($this->log[$unid]['users'][$userID])) {
			$this->log[$unid]['users'][$userID] = 1;
		} else {
			$this->log[$unid]['users'][$userID]++;
		}
	}
	
	/**
	 *	Add a user-hit for the given data.
	 *
	 *	@param array() $data The data to use for creating a hit.
	 *	@param string*32 $unid The internal UNID to use for the URI (a UNID is generated if no ID is supplied).
	 *	@param string*32 $unid The internal UNID to use for the user (a UNID is generated if no ID is supplied).
	 */
	private function _add_session_hit(&$data,$uriRef='',$unid='') {
		if ($uriRef == '') {
			$uriRef = $this->_get_hash($data,'URI');
		}
		if ($unid == '') {
			$unid = $this->_get_user_unid($data);
		}
		
		$sessionID = $this->sessions[$unid]['id'];
		if (!isset($this->log[$uriRef]['sessions'][$sessionID])) {
			$this->log[$uriRef]['sessions'][$sessionID] = 1;
		} else {
			$this->log[$uriRef]['sessions'][$sessionID]++;
		}
	}
	
	/**
	 *	Add a hit for the given data.
	 *
	 *	@param array() $data The data to use for creating a hit.
	 *	@param string*32 $unid The internal UNID to use (a UNID is generated if no ID is supplied).
	 */
	private function _add_hit(&$data,$unid='') {
		if ($unid == '') {
			$unid = $this->_get_hash($data,'URI');
		}
		
		if (!isset($this->log[$unid])) {
			$this->_new_log_entry($data,$unid);
		} else {
			$this->log[$unid]['hits']++;
		}
	}
	
	/**
	 *	Add a exit-hit for the given URI.
	 *	
	 *	@param string*32 $unid The userID to get the URI from.
	 */
	private function _add_exit($unid) {
		$uriRef = $this->_get_hash($this->sessions[$unid],'lastpage');
		
		if (isset($this->log[$uriRef])) {
            $this->log[$uriRef]['exit']++;
        }
	}
	
	/**
	 *	Add a bounce-hit for the given URI.
	 *	
	 *	@param string*32 $unid The userID to get the URI from.
	 */
	private function _add_bounce($unid) {
		$uriRef = $this->_get_hash($this->sessions[$unid],'lastpage');
		
		if (isset($this->log[$uriRef])) {
            $this->log[$uriRef]['bounce']++;
        }
	}
	
	/**
	 *	Add a entrance-hit for the given URI.
	 *	
	 *	@param array() $data The data to use for creating a hit.
	 *	@param string*32 $unid The internal UNID to use (a UNID is generated if no ID is supplied).
	 */
	private function _add_entrance($data,$unid='') {
		if ($unid == '') {
			$unid = $this->_get_hash($data,'URI');
		}
		
		$this->log[$unid]['entrance']++;
	}
	
	/**
	 *	Add processing-time for the given data.
	 *
	 *	@param array() $data The data to used for creating processing-time log.
	 *	@param string*32 $unid The internal UNID to use (a UNID is generated if no ID is supplied).
	 */
	private function _add_processing_time(&$data,$unid='') {
		if ($unid == '') {
			$unid = $this->_get_hash($data,'URI');
		}
		
		if (isset($data['processing_time'])) {
			if ($this->log[$unid]['server_time'] == 0) {
				$this->log[$unid]['server_time'] = $data['processing_time'];
			} else {
				$average = (($this->log[$unid]['server_time']+$data['processing_time'])/2);
				$this->log[$unid]['server_time'] = $average;
			}
		}
	}
	
	/**
	 *	Add a the page time for a URI.
	 *	
	 *	@param string*32 $unid The userID to get the URI from.
	 *	@param int $now The current time we are processing.
	 */
	private function _add_page_time($unid,$now) {
		$uriRef = $this->_get_hash($this->sessions[$unid],'lastpage');
		
		if (isset($this->log[$uriRef])) {
            $period = abs($now - $this->sessions[$unid]['time']);
            if ($this->log[$uriRef]['time'] == 0) {
                $this->log[$uriRef]['time'] = $period;
            } else {
                $this->log[$uriRef]['time'] = (($period+$this->log[$uriRef]['time'])/2);
            }
        }
	}
	
	private function _handle_sessions(&$data,$unid='') {
		if ($unid == '') {
			$unid = $this->_get_user_unid($data);
		}
		
		$sessionID = '';
		$now = $data['datetime']->epoc;
		if (!isset($this->sessions[$unid])) {
			$sessionID = $this->_add_new_session($data,$unid);
			$this->_add_entrance($data);
		} else {
			$sessionID = $this->sessions[$unid]['id'];
			
			if (($this->sessions[$unid]['time']+(30*60)) < $now) {
				$this->_add_exit($unid);
				$this->_add_entrance($data);
				$sessionID = $this->_get_hash(microtime());
				$this->sessions[$unid]['id'] = $sessionID;
				if ($this->sessions[$unid]['count'] == 1) {
					$this->_add_bounce($unid);
				}
				$this->sessions[$unid]['count'] = 1;
			} else {
				$this->sessions[$unid]['count']++;
				$this->_add_page_time($unid,$now);
			}
		}
		
		$this->_set_session_start($unid,$now);
		$this->sessions[$unid]['lastpage'] = $data['URI'];
		
		return $sessionID;
	}
	
	/**
	 *	Generate a new session
	 *
	 *	@return string*32 The generated sessionID.
	 */
	private function _add_new_session(&$data,$unid='') {
		if ($unid == '') {
			$unid = $this->_get_user_unid($data);
		}
		
		$sessionRef = $this->_get_hash(microtime());
		$this->sessions[$unid] = array(
			'id'=>$sessionRef, 'count' => 1
		);
		
		return $sessionRef;
	}
	
	private function _set_session_start($unid,$time) {
		$this->sessions[$unid]['time'] = $time;
	}
	
	/**
	 *	Add a blank entry for the given data.
	 *
	 *	@private
	 *	@param array() $data The data to use for creating a entry.
	 *	@param string*32 $unid The internal reference for a given URI.
	 */
	private function _new_log_entry(&$data,$unid) {
		$uriRef = $this->_get_hash($data['URI']);
		if (!isset($this->log[$unid])) {
			$this->log[$unid] = array(
				'value' => $data['URI'], 'hits' => 1,
				'users' => array(), 'sessions' => array(),
				'entrance' => 0, 'exit' => 0,
				'bounce' => 0, 'time' => 0,
				'server_time' => 0
			);
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
	private function _get_user_unid(&$data) {
		$userId = false;
		
		if ($this->useGoogleAnalytics) {
			$userId = $this->_get_google_analytics_user($data);
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
	private function _get_google_analytics_user(&$data) {
		if (isset($data['cookie'])) {
			if (isset($data['cookie']['__utma'])) {
				return $this->_get_hash($data['cookie']['__utma']);
			}
		}
		
		return false;
	}
	
	/**
	 *	Generate the original URI from the supplied data and store in given array.
	 *
	 *	@private
	 *	@param array() $data The data to use.
	 *	@param string $itemName The item-name to store the URL under.
	 *	@return Filesystem_Path The URI.
	 */
	private function _store_uri(&$data,$itemName='URI') {
		$url = $data['domain'].$data['request'];
		$count = preg_match('/\A(.*?)(?:\/|\Z)/',$data['protocol'],$matches);
		if ($count > 0) {
		$url = $matches[1].'://'.$url;
		}
		$url = new Filesystem_Path(strtolower($url));
		
		$data[$itemName] = $url;
		return trim($url);
	}
}
?>