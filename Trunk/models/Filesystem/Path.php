<?php
defined('DIRECT_ACCESS_CHECK') or die;

/*
 *	File handling class
 *
 *	File Open/Close and parsing operations.  Will open files, navigate through
 *	them and parse them according to installed sub-classes.
 *	
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.8
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Filesystem
 */
class Filesystem_Path extends Base {
	const FSLASH = "/";
	const BSLASH = "\\";
	
	private $parts;
	
	public function __construct($path='',$filename='') {
		if ($path != '') {
			$this->_init($path,$filename);
		}
	}
	
	private function __init($path,$filename='') {
		$this->_reset_parts();
		$testPath = self::_fix_bad_path($path.self::FSLASH.$filename);
		
		$this->parts['scheme'] = self::get_scheme($testPath);
		$this->parts['username'] = self::get_username($testPath);
		$this->parts['password'] = self::get_password($testPath);
		$this->parts['domain'] = self::get_domain($testPath);
		$this->parts['port'] = self::get_port($testPath);
		$this->parts['path'] = self::get_path($testPath);
		$this->parts['query'] = self::get_query($testPath);
		$this->parts['fragment'] = self::get_fragment($testPath);
	}
	
	public static function get_domain($url) {
		$match = preg_match('/\A[a-z][a-z0-9+\-.]+:\/\/(?:.*?@|)(.*?)(?:\/|\Z|\:)/',$url,$matches);
		if ($match == 0) {
			return false;
		} else {
			return $matches[1];
		}
	}
	
	public static function get_scheme($url) {
		$match = preg_match('/\A([a-z][a-z0-9+\-.]+):\/\//',$url,$matches);
		if ($match == 0) {
			return false;
		} else {
			return $matches[1];
		}
	}
	
	public static function get_username($url) {
		$match = preg_match('/\A[a-z0-9+\-.]+:\/\/([a-z0-9\-._~%!$&\'()*+,;=]+)(?:@|\:)/',$url,$matches);
		if ($match == 0) {
			return false;
		} else {
			return $matches[1];
		}
	}
	
	public static function get_password($url) {
		$match = preg_match('/\A[a-z0-9+\-.]+:\/\/[a-z0-9\-._~%!$&\'()*+,;=]+\:(.*?)@/',$url,$matches);
		if ($match == 0) {
			return false;
		} else {
			return $matches[1];
		}
	}
	
	public static function get_port($url) {
		$match = preg_match('/\A[a-z0-9+\-.]+:\/\/(?:.*?@|).*?\:(\d+)(?:\/|\Z)/',$url,$matches);
		if ($match == 0) {
			return false;
		} else {
			return (int) $matches[1];
		}
	}
	
	public static function get_path($url) {
		if (self::_has_scheme($url)) {
			$match = preg_match('/\A[a-z0-9+\-.]+:\/\/.*?\/(.*?)(?:#|\?|\Z)/',$url,$matches);
			if ($match == 0) {
				return false;
			} else {
				return self::FSLASH.$matches[1];
			}
		} else {
			$match = preg_match('/\A([\/]*?).*/',$url,$matches);
			if ($match == 0) {
				return false;
			} else {
				print_r($matches);
				$url = $matches[1].implode(self::FSLASH,self::_explode_path($url));
			}
			
			return $url;
		}
	}
	
	public static function get_query($url) {
		$url = self::_fix_bad_path($url);
		$match = preg_match('/\A[^?#]+\?([^#]+)/',$url,$matches);
		if ($match == 0) {
			return false;
		} else {
			return self::_parse_query($matches[1]);
		}
	}
	
	private static function _parse_query($query) {
		$parsed = array();
		$parts = str_replace('&amp;','&',$query);
		$parts = explode('&',$parts);
		foreach ($parts as $part) {
			$qparts = explode('=',$part);
			if (count($qparts) == 1) {
				$parsed[$qparts[0]] = '';
			} elseif (count($qparts) == 2) {
				$parsed[$qparts[0]] = $qparts[1];
			}
		}
			
		return $parsed;
	}
	
	public static function get_fragment($url) {
		$url = self::_fix_bad_path($url);
		$match = preg_match('/#(.+)/',$url,$matches);
		if ($match == 0) {
			return false;
		} else {
			return $matches[1];
		}
	}
	
	private function _reset_parts() {
		$parts = array(
			'scheme' => false, 'username' => false, 'passsword' => false,
			'domain' => false, 'port' => false, 'path' => false,
			'query' => false, 'fragment' => false
		);
	}
	
	private function set_path($path,$filename='') {
		$this->_init($path,$filename);
	}
	
	private static function _explode_path($path,$filename='') {
		$parts = array();
		$path = self::_fix_bad_path($path.self::FSLASH.$filename);
		$parts1 = explode(self::FSLASH,$path);
		
		foreach ($parts1 as $part1) {
			$parts2 = explode(self::BSLASH,$part1);
			foreach ($parts2 as $part2) {
				array_push($parts,$part2);
			}
		}
		$parts = I::array_trim($parts);
		
		return $parts;
	}
	
	private static function _fix_bad_path($url) {
		$url = self::_fix_bad_query($url);
		$url = self::_fix_bad_fragment($url);
		$url = self::_fix_bad_at($url);
		return $url;
	}
	
	private static function _fix_bad_query($url) {
		$parts = explode('?',$url);
		if (count($parts) > 2) {
			$url = array_shift($parts).'?';
			$url = $url.implode('%3F',$parts);
		}
		return $url;
	}
	
	private static function _fix_bad_fragment($url) {
		$parts = explode('#',$url);
		if (count($parts) > 2) {
			$url = array_shift($parts).'#';
			$url = $url.implode('%23',$parts);
		}
		return $url;
	}
	
	private static function _fix_bad_at($url) {
		$parts = explode('@',$url);
		if (count($parts) > 2) {
			$url = array_shift($parts).'@';
			$url = $url.implode('%40',$parts);
		}
		return $url;
	}
	
	private static function _has_scheme($url) {
		$match = preg_match('/\A[A-Za-z0-9]+\:\/\//',$url);
		return (($match==1)?true:false);
	}
	
	private static function _has_password($url) {
		$match = preg_match('/\A[A-Za-z0-9]+\:\/\/[^\/]+\:[^\/]+\@/',$url);
		return (($match==1)?true:false);
	}
	
	private static function _has_username($url) {
		$match = preg_match('/\A[A-Za-z0-9]+\:\/\/[^\/]+\@/',$url);
		return (($match==1)?true:false);
	}
	
	private static function _has_port($url) {
		$match = preg_match('/\A[A-Za-z0-9]+\:\/\/([^\/]+)/',$url,$matches);
		if ($match == 1) {
			$domain = $matches[1];
			$parts = explode('@',$matches[1]);
			if (count($parts) == 2) {
				$domain = $parts[1];
			}
			
			if (I::contains($domain,':')) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 *	Generic get property method.
	 *
	 *	Get the value of an application property.  Values are stored in
	 *	the application array and accessed via the __set and __get methods.
	 *
	 *	@public
	 */
	public function __get($property) {
		$convertedProperty = I::camelize($property);
		if (isset($this->parts[$convertedProperty])) {
			return $this->parts[$convertedProperty];
		} else {
			throw new Exception('Property: '.$convertedProperty.', does not exist');
		}
	}
}
?>