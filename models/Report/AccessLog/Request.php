<?php
defined('DIRECT_ACCESS_CHECK') or die;

/*
 *	Report_Request
 *	
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.2
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Report
 */
class Report_AccessLog_Request extends Report_ReportBase implements Iterator {
	public $userManager;
	
	/**
	 *	Intialize the object.
	 *
	 *	@private
	 */
	public function init($userManager) {
		$this->userManager = $userManager;
		$this->userManager->attach_event('onNewSession',$this,'new_session');
		$this->userManager->attach_event('onEndSession',$this,'end_session');
		$this->userManager->attach_event('onNewUser',$this,'new_user');
	}
	
	/**
	 *	Close all open session.
	 *
	 *	Close all sessions in the connected Report_UserManager object.
	 *
	 *	@public
	 */
	public function close_all_sessions() {
		$this->userManager->close_all_sessions();
	}
	
	/**
	 *	Parse the given data for the request report.
	 *
	 *	@public
	 *	@param array() $data The data to use.
	 */
	public function parse($data) {
		$uri = $this->_get_uri($data);
		$uriRef = $this->_get_hash($uri);
		
		if (!isset($this->report[$uriRef])) {
			$this->report[$uriRef] = $this->_create_report_entry();
			$this->report[$uriRef]['uri'] = $uri;
		}
		$this->report[$uriRef]['hits']++;
		$userId = $this->userManager->parse($data);
		$this->report[$uriRef]['users'][$userId] = true;
		$user = $this->userManager->get_user($userId);
		$this->report[$uriRef]['sessions'][$user->sessionId] = true;
		
	}
	
	/**
	 *	Move to the next report.
	 *
	 *	Move and return it, part of Iterator object. Overides the default
	 *	next() method defined in Report_ReportBase.
	 *
	 *	@public
	 *	@return Report
	 */
	public function next() {
		$key = $this->order[$this->position];
		$this->current = $this->report[$key];
		
		$count = count($this->current['users']);
		$this->current['users'] = $count;
		
		$count = count($this->current['sessions']);
		$this->current['sessions'] = $count;
		
		$this->position++;
		return $this->current;
	}
	
	/**
	 *	Get the report column headers.
	 *
	 *	Get the report column headers, which is extremely useful when printing
	 *	CSV to a file.
	 *
	 *	@public
	 *	@return array()
	 */
	public function headers() {
		return array_keys($this->_create_report_entry());
	}
	
	/**
	 *	Create an array for storing on item of data.
	 *
	 *	@private
	 *	@return array() The data-array for a report entry.
	 */
	private function _create_report_entry() {
		return array(
			'uri' => '', 'hits' => 0, 'users' => array(),
			'sessions' => array(), 'entrance' => 0, 'exit' => 0,
			'bounce' => 0
		);
	}
    
	/**
	 *	Handle a new session event.
	 *
	 *	This function is called when a new session is created in the
	 *	Report_UserManager object being used by the report suit.
	 *
	 *	@public
	 *	@param Report_UserManager_User $user The user-object for the new session.
	 */
    public function new_session($user) {
        $uri = $user->uri;
		$uriRef = $this->_get_hash($uri);
		if (isset($this->report[$uriRef])) {
			$this->report[$uriRef]['entrance']++;
		}
    }
    
	/**
	 *	Handle a session termination event.
	 *
	 *	This function is called when a session is ended in the
	 *	Report_UserManager object being used by the report suit.
	 *
	 *	@public
	 *	@param Report_UserManager_User $user The user-object for the terminated session.
	 */
    public function end_session($user) {
        $uri = $user->uri;
		$uriRef = $this->_get_hash($uri);
		if (isset($this->report[$uriRef])) {
			$this->report[$uriRef]['exit']++;
			if ($user->count == 1) {
				$this->report[$uriRef]['bounce']++;
			}
		}
    }
    
	/**
	 *	Handle a user creation event.
	 *
	 *	This function is called when a new user is added in the
	 *	Report_UserManager object being used by the report suit.
	 *
	 *	@public
	 *	@param Report_UserManager_User $user The user-object for the new user.
	 */
    public function new_user($user) {
		
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
	 *	Destructor
	 *
	 *	Ensures that session is closed when the object is destroyed. Closing
	 *	session will call the onEndSession event.
	 */
	public function __destruct() {
		$this->userManager->close_all_sessions();
    }
}
?>