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
class Report_Request extends Report_ReportBase {
	public $userManager;
	
	public function init($userManager) {
		$this->userManager = $userManager;
		$this->userManager->attach_event('onNewSession',$this,'new_session');
		$this->userManager->attach_event('onEndSession',$this,'end_session');
		$this->userManager->attach_event('onNewUser',$this,'new_user');
	}
	
	public function close_all_sessions() {
		$this->userManager->close_all_sessions();
	}
	
	public function parse(&$data) {
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
    
    public function new_session($user) {
        $uri = $user->uri;
		$uriRef = $this->_get_hash($uri);
		if (isset($this->report[$uriRef])) {
			$this->report[$uriRef]['entrance']++;
		}
    }
    
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
    
    public function new_user($user) {
		
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
}
?>