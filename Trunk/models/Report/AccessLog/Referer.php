<?php
defined('DIRECT_ACCESS_CHECK') or die;

/*
 *	Report_Request
 *	
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Report
 */
class Report_AccessLog_Referer extends Report_ReportBase implements Iterator {
	public $userManager;
	
	/**
	 *	Initialize the object.
	 *
	 *	@private
	 */
	public function init($userManager) {
		$this->userManager = $userManager;
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
	 *	Parse the given data for the referer report.
	 *
	 *	@public
	 *	@param array() $data The data to use.
	 */
	public function parse($data) {
		if ($this->_is_entrance($data)) {
			$uri = $this->_get_referer_uri($data)->domain;
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
	}
	
	/**
	 *	Test whether the user is entering the domain.
	 *
	 *	Compare request and referer domain to see if the user has entered
	 *	the domain with this request or was already active on the site.
	 *
	 *	@private
	 *	@param array() $data The data to use.
	 *	@return boolean
	 */
	public function _is_entrance($data) {
		$request = str_replace(
			'www.','',$this->_get_request_uri($data)->domain
		);
		$referer = str_replace(
			'www.','',$this->_get_referer_uri($data)->domain
		);
		
		return (strtolower($request) != strtolower($referer));
	}
	
	public function next() {
		$key = $this->order[$this->position];
		$this->current = $this->report[$key];
		$this->current['users'] = count($this->current['users']);
		$this->current['sessions'] = count($this->current['sessions']);
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
			'uri' => '', 'hits' => 0,
			'users' => array(), 'sessions' => array()
		);
	}
    
	/**
	 *	Generate the original URI from the supplied data.
	 *
	 *	@private
	 *	@param array() $data The data to use.
	 *	@return Filesystem_Path The URI.
	 */
	private function _get_request_uri($data) {
		$url = $data['domain'].$data['request'];
		$count = preg_match('/\A(.*?)(?:\/|\Z)/',$data['protocol'],$matches);
		if ($count > 0) {
			$url = $matches[1].'://'.$url;
		}
		$url = new Filesystem_Path(strtolower($url));
		
		return $url;
	}
	
	/**
	 *	Get the referer URI.
	 *
	 *	From the supplied data array, return a Filesystem_Path object, which
	 *	represents the referer
	 *
	 *	@private
	 *	@param array() $data The data to parse for a referer URI.
	 *	@return Filesystem_Path
	 */
	private function _get_referer_uri($data) {
		$url = $data['referer'];
		$url = new Filesystem_Path(strtolower($url));
		
		return $url;
	}
}
?>