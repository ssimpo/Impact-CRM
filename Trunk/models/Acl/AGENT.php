<?php
/*
 *	Class for testing against a geographic location using MaxMind's GeoIP
 *	database.
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Impact
 */
class Acl_AGENT extends Acl_TestBase implements Acl_Test {
    private $lookup = array();
    private $agent;
   
    public function __construct($application=null) {
        if(!defined('DS')) { define('DS',DIRECTORY_SEPARATOR); }
	$this->agent = $_SERVER['HTTP_USER_AGENT'];
    }
    
    private function _get_browser_info($agent) {
	if (!isset($this->lookup[$agent])) {
	    try {
		$this->lookup[$agent] = get_browser($agent);
	    } catch(Exception $e) {
		require_once ROOT_BACK.'includes'.DS.'browscap'.DS.'Browscap.php';
		$browscap = new Browscap(ROOT_BACK.'databases'.DS.'browscap.ini');
		$this->lookup[$agent] = $browscap->get_browser($agent);
	    }
	}
	return $this->lookup[$agent];
    }
    
    public function _test_browser($attributes) {
	$browscap = $this->_get_browser_info($this->agent);
        return (strtoupper($browscap->browser) == strtoupper($attributes[0]));
    }
    
    public function _test_platform($attributes) {
        $browscap = $this->_get_browser_info($this->agent);
        return (strtoupper($browscap->platform) == strtoupper($attributes[0]));
    }
    
    public function _test_mobile($attributes) {
        $browscap = $this->_get_browser_info($this->agent);
        return ($browscap->ismobiledevice == 1);
    }
}
?>