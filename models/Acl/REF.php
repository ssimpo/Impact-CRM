<?php
if (!defined('DIRECT_ACCESS_CHECK')) {
	die('Direct access is not allowed');
}

/*
 *	Class for testing against a geographic location using MaxMind's GeoIP
 *	database.
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.2
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Impact
 */
class Acl_REF extends Acl_TestBase implements Acl_Test {
    public $application;
    
    /**
     *	Constructor.
     *
     *	@public
     *	@param object $application The current (or other) application object.
     *	@return object Acl_REF
     */
    public function __construct($application=null) {
		if (!defined('DS')) {
			define('DS',DIRECTORY_SEPARATOR);
		}
		if (!is_null($application)) {
			$this->application = $application;
            $this->_set_referer();
		}
    }
	
	/**
	 *	Generic get property method.
	 *
	 *	Used to dynamically get a property based on live setup.
	 *
	 *	@public
	 */
	public function __get($property) {
		switch ($property) {
			case 'referer':
				return $this->_get_referer();
				break;
			default:
				return null;
		}
	}
	
	/**
	 *	Get the referer property.
	 *
	 *	@private
	 *	@return string IP value.
	 */
	private function _get_referer() {
		if (!is_null($this->application)) {
			return $this->application->referer;
		} else {
			if (isset($_SERVER['HTTP_REFERER'])) {
				return $_SERVER['HTTP_REFERER'];
			} else {
				return null;
			}
		}
	}
	
	/**
	 *	Set the referer property of the application object.
	 *	
	 *	@private
	 */
	private function _set_referer() {
		if (!property_exists($this->application,'referer')) {
			if (!$this->application->property_exists('referer')) {
				if (isset($_SERVER['HTTP_REFERER'])) {
					$this->application->referer = $_SERVER['HTTP_REFERER'];
				}
			} else {
				$this->_set_referer_null_check();
			}
		} else {
			$this->_set_referer_null_check();
		}
	}
	
	/**
	 *	Check if the referer property is null/blank and populate if so.
	 *
	 *	@private
	 */
	private function _set_referer_null_check() {
		if ((is_null($this->application->referer)) || ($this->application->referer == '')) {
			if (isset($_SERVER['HTTP_REFERER'])) {
				$this->application->referer = $_SERVER['HTTP_REFERER'];
			}
		}
	}
	
    /**
     *	Get the search-keywords from a URL
     *
     *	@private
     *	@param string $url The URL to grab query from.
     *	@return array
     */
    private function _get_query($url = null) {
		$parts = parse_url($url);
		$query = isset($parts['query']) ? $parts['query'] : (isset($parts['fragment']) ? $parts['fragment'] : '');
		if (!$query) {
			return '';
		}
		parse_str($query, $parts);
		return explode(
			' ',
			strtoupper(isset($parts['q']) ? $parts['q'] : (isset($parts['p']) ? $parts['p'] : ''))
		);
    }

	/**
	*	Test whether the user arrived at the page via a url with specified keywords in the query.
	*
    *	@protected
    *	@param array $attributes The keywords as a array-list.
    *	@return boolean
    */
	public function test_keywords($attributes) {
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