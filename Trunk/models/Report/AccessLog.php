<?php
defined('DIRECT_ACCESS_CHECK') or die;

/*
 *	Report_AccessLog
 *	
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Report
 */
class Report_AccessLog extends Report_ReportBase implements Iterator {
	public $userManager;
	public $reportName;
	
	public function __construct() {
        $this->_init();
    }
	
	private function _init() {
		$this->userManager = new Report_UserManager();
		$this->userManager->useGoogleAnalytics = true;
		$this->report = array();
		$this->reportName = array();
	}
	
	public function close_all_sessions() {
		$this->userManager->close_all_sessions();
	}
	
	public function reset() {
		$this->_init();
	}
	
	public function key() {
		return $this->reportName[parent::key()];
	}
	
	public function parse($data) {
		if ($data instanceof Filesystem_File_Object) {
			return $this->_parse_file($data);
		} else {
			foreach ($this->report as $report) {
				$report->parse($data);
			}
			return 1;
		}
	}
	
	private function _parse_file($file) {
		$counter = 0;
		foreach ($file as $line) {
			$this->parse($line);
			$counter++;
		}
		return $counter;
	}
	
	public function get_report($type) {
		$type = strtolower('report_accesslog_'.$type);
		$ref = $this->_get_hash($type);
		if (isset($this->report[$ref])) {
			return $this->report[$ref];
		} else {
			return false;
		}
	}
	
	public function add_report($type) {
		$ntype = strtolower('report_accesslog_'.$type);
		$ref = $this->_get_hash($ntype);
		$this->reportName[$ref] = $type;
		$this->report[$ref] = $this->factory($ntype);
		if ((!is_object($this->report[$ref])) || ($ntype != strtolower(get_class($this->report[$ref])))) {
			return false;
		}
		
		$this->report[$ref]->init($this->userManager);
		
		return true;
	}
	
	public function remove_report($type) {
		$type = strtolower('report_accesslog_'.$type);
		$ref = $this->_get_hash($type);
		if (isset($this->report[$ref])) {
			unset($this->report[$ref]);
			unset($this->reportName[$ref]);
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
	protected function _get_hash($data,$itemName='') {
		if ($itemName != '') {
			return md5((string) $data[$itemName]);
		} else {
			return md5((string) $data);
		}
	}
}
?>