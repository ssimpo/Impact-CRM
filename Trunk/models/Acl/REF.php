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
class Acl_REF extends Acl_TestBase implements Acl_Test {
    public $referer;
    
    /**
     *		Constructor.
     *
     *		@public
     *		@param object $application The current (or other) application object.
     *		@return object Acl_REF
     */
     public function __construct($application=null) {
        if(!defined('DS')) { define('DS',DIRECTORY_SEPARATOR); }
	$this->referer = $_SERVER['HTTP_REFERER'];
    }
    
    function _get_query($url = null) {
	$parts = parse_url($url);
	$query = isset($parts['query']) ? $parts['query'] : (isset($parts['fragment']) ? $parts['fragment'] : '');
	if(!$query) {
	    return '';
	}
	parse_str($query, $parts);
	return explode(
	    ' ',
	    strtoupper(isset($parts['q']) ? $parts['q'] : (isset($parts['p']) ? $parts['p'] : ''))
	);
    }

    
    public function _test_keywords($attributes) {
	$found = true;
	$keywords = $this->_get_query($this->referer);
	foreach ($attributes as $keyword) {
	    if (!in_array(strtoupper($keyword),$keywords)) {
		$found = false;
	    }
	}
        return $found;
    }
}
?>