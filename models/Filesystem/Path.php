<?php
defined('DIRECT_ACCESS_CHECK') or die;

/*
 *	File handling class
 *
 *	File Open/Close and parsing operations.  Will open files, navigate through
 *	them and parse them according to installed sub-classes.
 *
 *	@todo Handle file:///
 *	@todo Handle mailto: ?  It is questionable whether this should be parsed but it would be useful as it is something, which may fit well in overa-all URL parsing.
 *	@todo Return paths using native directory separator.
 *	
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.0
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Filesystem
 */
class Filesystem_Path extends Base {
	const FSLASH = "/";
	const BSLASH = "\\";
	
	private $parts = array();
	
	public function __construct($path='',$filename='') {
		$this->_reset_parts();
		if ($path != '') {
			$this->_init($path,$filename);
		}
	}
	
	private function _init($path,$filename='') {
		$this->_reset_parts();
		$testPath = $path;
		if ($filename != '') {
			$testPath = $path.self::FSLASH.$filename;
		}
		$testPath = self::_fix_bad_path($testPath);
		
		$this->parts['scheme'] = self::get_scheme($testPath);
		$this->parts['username'] = self::get_username($testPath);
		$this->parts['password'] = self::get_password($testPath);
		$this->parts['domain'] = self::get_domain($testPath);
		$this->parts['port'] = self::get_port($testPath);
		$this->parts['computer'] = self::get_computer($testPath);
		$this->parts['share'] = self::get_share($testPath);
		$this->parts['drive'] = self::get_drive($testPath);
		$this->parts['path'] = self::get_path($testPath);
		$this->parts['filename'] = self::get_filename($testPath);
		$this->parts['extension'] = self::get_extension($testPath);
		$this->parts['query'] = self::get_query($testPath);
		$this->parts['fragment'] = self::get_fragment($testPath);
	}
	
	/**
	 *	Reset the the internal array, which holds the parts of the current URI/URL/UNC/Local-path.
	 *
	 *	@private
	 */
	private function _reset_parts() {
		$this->parts = array(
			'scheme' => false, 'username' => false, 'passsword' => false,
			'domain' => false, 'port' => false, 'computer' => '',
			'share' => '', 'drive' => '', 'path' => false,
			'filename' => '', 'Extension' => '', 'query' => false,
			'fragment' => false
		);
	}
	
	/**
	 *	Set the path and filename we wish to parse.
	 *
	 *	Will set the path and filename for parsing.  Each can be given as
	 *	fragments of the over-all path; this increases the flexibility of
	 *	the parser.  Hence, $path can be a path to a directory or resource
	 *	and $filename can be the path to the file relative to $path.
	 *
	 *	@public
	 *	@param string $path The URL/URI/UNC/Local-path.
	 *	@param string $filename The filename or a URL/URI/UNC/Local-path fragment.
	 *	@return string
	 */
	public function set_path($path,$filename='') {
		$this->_init($path,$filename);
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
	
	/**
	 *	Get the scheme if available from a supplied URL/URI/UNC/Local-path.
	 *
	 *	@public
	 *	@static
	 *	@param string $url The URL/URI/UNC/Local-path.
	 *	@return string
	 */
	public static function get_scheme($url) {
		$match = preg_match('/\A([a-z][a-z0-9+\-.]+):\/\//',$url,$matches);
		if ($match == 0) {
			return false;
		} else {
			return $matches[1];
		}
	}
	
	/**
	 *	Get the domain if available from a supplied URL/URI/UNC/Local-path.
	 *
	 *	@public
	 *	@static
	 *	@param string $url The URL/URI/UNC/Local-path.
	 *	@return string
	 */
	public static function get_domain($url) {
		$match = preg_match('/\A[a-z][a-z0-9+\-.]+:\/\/(?:.*?@|)(.*?)(?:\/|\Z|\:)/',$url,$matches);
		if ($match == 0) {
			return false;
		} else {
			return $matches[1];
		}
	}
	
	/**
	 *	Get the username if available from a supplied URL/URI.
	 *
	 *	@public
	 *	@static
	 *	@param string $url The URL/URI.
	 *	@return string
	 */
	public static function get_username($url) {
		$match = preg_match('/\A[a-z0-9+\-.]+:\/\/([a-z0-9\-._~%!$&\'()*+,;=]+)(?:@|\:)/',$url,$matches);
		if ($match == 0) {
			return false;
		} else {
			return $matches[1];
		}
	}
	
	/**
	 *	Get the password if available from a supplied URL/URI.
	 *
	 *	@public
	 *	@static
	 *	@param string $url The URL/URI.
	 *	@return string
	 */
	public static function get_password($url) {
		$match = preg_match('/\A[a-z0-9+\-.]+:\/\/[a-z0-9\-._~%!$&\'()*+,;=]+\:(.*?)@/',$url,$matches);
		if ($match == 0) {
			return false;
		} else {
			return $matches[1];
		}
	}
	
	/**
	 *	Get the port if available from a supplied URL/URI.
	 *
	 *	@public
	 *	@static
	 *	@param string $url The URL/URI.
	 *	@return int
	 */
	public static function get_port($url) {
		$match = preg_match('/\A[a-z0-9+\-.]+:\/\/(?:.*?@|).*?\:(\d+)(?:\/|\Z)/',$url,$matches);
		if ($match == 0) {
			return false;
		} else {
			return (int) $matches[1];
		}
	}
	
	/**
	 *	Get the computer if available from a supplied UNC.
	 *
	 *	@public
	 *	@static
	 *	@param string $unc The UNC.
	 *	@return string
	 */
	public static function get_computer($unc) {
		$count = self::_count_leading_slashes($unc);
		if ($count != 2) {
			return false;
		} else {
			$parts = self::_explode_path($unc);
			if (!empty($parts)) {
				return array_shift($parts);
			}
		}
		
		return false;
	}
	
	/**
	 *	Get the share if available from a supplied UNC.
	 *
	 *	@public
	 *	@static
	 *	@param string $unc The UNC.
	 *	@return string
	 */
	public static function get_share($unc) {
		$count = self::_count_leading_slashes($unc);
		if ($count != 2) {
			return false;
		} else {
			$parts = self::_explode_path($unc);
			if (count($parts) > 1) {
				return $parts[1];
			}
		}
		
		return false;
	}
	
	/**
	 *	Get the windows drive-letter if available from a supplied URL/URI/UNC/Local-path.
	 *
	 *	@public
	 *	@static
	 *	@param string $url The URL/URI/UNC/Local-path.
	 *	@return string
	 */
	public static function get_drive($url) {
		$match = preg_match('/\A([A-Z])\:/i',$url,$matches);
		if ($match == 0) {
			return false;
		} else {
			if (!self::_has_scheme($url)) {
				return $matches[1];
			}
		}
		
		return false;
	}
	
	/**
	 *	Get the path if available from a supplied URL/URI/UNC/Local-path.
	 *
	 *	Will return the local path if it is a local-path/UNC and the remote
	 *	path if it is a URI/URL.
	 *
	 *	@public
	 *	@static
	 *	@param string $url The URL/URI/UNC/Local-path.
	 *	@return string
	 */
	public static function get_path($url) {
		if (self::_has_scheme($url)) {
			return self::_get_path_url($url);
		} else {
			return self::_get_path_local($url);
		}
	}
	
	/**
	 *	Get the path if available from a supplied URL/URI.
	 *
	 *	@private
	 *	@static
	 *	@param string $url The URL/URI.
	 *	@return string
	 */
	private static function _get_path_url($url) {
		$match = preg_match('/\A[a-z0-9+\-.]+:\/\/.*?\/(.*?)(?:#|\?|\Z)/',$url,$matches);
		if ($match == 0) {
			return false;
		} else {
			$parts = self::_get_path_array($matches[1]);
			$path = implode(self::FSLASH,$parts).self::FSLASH;
			$count = self::_count_leading_slashes($path);
			if ($count == 0) {
				$path = self::FSLASH.$path;
			}
			if ($path == self::FSLASH.self::FSLASH) {
				return self::FSLASH;
			}
			return $path;
		}
	}
	
	/**
	 *	Get the domain if available from a supplied UNC/Local-path.
	 *
	 *	@private
	 *	@static
	 *	@param string $path The UNC/Local-path.
	 *	@return string
	 */
	private static function _get_path_local($path) {
		$count = self::_count_leading_slashes($path);
		$parts = self::_get_path_array($path);
		$path = implode(self::FSLASH,$parts).self::FSLASH;
		$path = str_repeat(self::FSLASH,$count).$path;
		if (($count == 0) && ($path == self::FSLASH)) {
			return '';
		}
		if ($path == self::FSLASH.self::FSLASH) {
				return self::FSLASH;
			}
		return $path;
	}
	
	/**
	 *	Get a path as an array (removing the file part) from a URI/URL/UNC/Local-path.
	 *
	 *	@private
	 *	@static
	 *	@param string $path The URI/URL/UNC/Local-path.
	 *	@return array()
	 */
	private static function _get_path_array($path) {
		$parts = self::_explode_path($path);
		if (self::_has_scheme($path)) {
			$parts = self::_lchop_array($parts,2);
			if (empty($parts)) {
				return array();
			}
		}
		
		$count = self::_count_trailing_slashes($path);
		if ((!empty($parts)) && ($count == 0)) {
			array_pop($parts);
		}
		return $parts;
	}
	
	/**
	 *	Count the leading slashes in supplied UNC/Local-path.
	 *
	 *	@private
	 *	@static
	 *	@param string $path The UNC/Local-path.
	 *	@return int
	 */
	private static function _count_leading_slashes($path) {
		$count = 0;
		for ($i=0; $i < strlen($path); $i++) {
			$character = substr($path,$i,1);
			if (($character == self::BSLASH) || ($character == self::FSLASH)) {
				$count++;
			} else {
				break;
			}
		}
		return $count;
	}
	
	/**
	 *	Count the trailing slashes in supplied UNC/Local-path.
	 *
	 *	@private
	 *	@static
	 *	@param string $path The UNC/Local-path.
	 *	@return int
	 */
	private static function _count_trailing_slashes($path) {
		$path = self::_chop_query_and_fragment($path);
		
		$count = 0;
		for ($i=(strlen($path)-1); $i > 0; $i--) {
			$character = substr($path,$i,1);
			if (($character == self::BSLASH) || ($character == self::FSLASH)) {
				$count++;
			} else {
				break;
			}
		}
		return $count;
	}
	
	/**
	 *	Get the filename if available from a supplied URL/URI/UNC/Local-path.
	 *
	 *	@public
	 *	@static
	 *	@param string $url The URL/URI/UNC/Local-path.
	 *	@return string
	 */
	public static function get_filename($url) {
		if (self::_count_trailing_slashes($url) != 0) {
			return false;
		}
		
		$parts = self::_explode_path($url);
		if (empty($parts)) {
			return false;
		} else {
			if (self::_has_scheme($url)) {
				$parts = self::_lchop_array($parts,2);
				if (empty($parts)) {
					return false;
				}
			}
			$filepart = array_pop($parts);
			return self::_chop_query_and_fragment($filepart);
		}
	}
	
	/**
	 *	Get the filename-extension if available from a supplied URL/URI/UNC/Local-path.
	 *
	 *	@public
	 *	@static
	 *	@param string $url The URL/URI/UNC/Local-path.
	 *	@return string
	 */
	public static function get_extension($url) {
		$filename = self::get_filename($url);
		if (!$filename) {
			return false;
		} else {
			$position = strpos($filename,'.');
			if ($position === false) {
				return false;
			}
			return substr($filename,$position+1);
		}
	}
	
	/**
	 *	Remove a specified number of elements from the start of an array.
	 *
	 *	@private
	 *	@static
	 *	@param array $array The array to chop.
	 *	@param int $amount The amount to chop it by.
	 *	@return array();
	 */
	private static function _lchop_array($array,$amount) {
		for ($i=1; $i <= 2; $i++) {
			if (!empty($array)) {
				array_shift($array);
			}
		}
		return $array;
	}
	
	/**
	 *	Remove and query-string and fragment information from a string.
	 *
	 *	@private
	 *	@static
	 *	@param string $url The string containing possible query/fragment.
	 *	@return string
	 */
	private static function _chop_query_and_fragment($url) {
		$removes = array('?','#');
		foreach ($removes as $remove) {
			$postion = strpos($url,$remove);
			if ($postion !== false) {
				$url = substr($url,0,$postion);
			}
		}
		return $url;
	}
	
	/**
	 *	Get the query if available from a supplied URL/URI.
	 *
	 *	Will parse the query into an array of the form key1=value1, key2=value2.
	 *
	 *	@public
	 *	@static
	 *	@param string $url The URL/URI.
	 *	@return array()
	 */
	public static function get_query($url) {
		$url = self::_fix_bad_path($url);
		$match = preg_match('/\A[^?#]+\?([^#]+)/',$url,$matches);
		if ($match == 0) {
			return false;
		} else {
			return self::_parse_query($matches[1]);
		}
	}
	
	/**
	 *	Parse a query-string into an array.
	 *
	 *	@private
	 *	@static
	 *	@param string $query The query-string.
	 *	@return array()
	 */
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
	
	/**
	 *	Get the fragment if available from a supplied URL/URI.
	 *
	 *	@public
	 *	@static
	 *	@param string $url The URL/URI.
	 *	@return string
	 */
	public static function get_fragment($url) {
		$url = self::_fix_bad_path($url);
		$match = preg_match('/#(.+)/',$url,$matches);
		if ($match == 0) {
			return false;
		} else {
			return $matches[1];
		}
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
	
	/**
	 *	Attempt to fix a badly configured URI/URL/UNC/Local-path.
	 *
	 *	@private
	 *	@static
	 *	@param string $url The URL/URI.
	 *	@return string
	 */
	private static function _fix_bad_path($url) {
		$url = self::_fix_bad_query($url);
		$url = self::_fix_bad_fragment($url);
		$url = self::_fix_bad_at($url);
		$url = self::_fix_bad_slashes($url);
		
		return $url;
	}
	
	/**
	 *	Attempt to fix a badly configured slashes within a URI/URL.
	 *
	 *	Will convert all slashes to forward-slash so there is consistancy.
	 *
	 *	@private
	 *	@static
	 *	@param string $url The URL/URI.
	 *	@return string
	 */
	private static function _fix_bad_slashes($url) {
		return str_replace(self::BSLASH,self::FSLASH,$url);
	}
	
	/**
	 *	Attempt to fix a badly configured query within a URI/URL.
	 *
	 *	@private
	 *	@static
	 *	@param string $url The URL/URI.
	 *	@return string
	 */
	private static function _fix_bad_query($url) {
		$parts = explode('?',$url);
		if (count($parts) > 2) {
			$url = array_shift($parts).'?';
			$url = $url.implode('%3F',$parts);
		}
		return $url;
	}
	
	/**
	 *	Attempt to fix a badly configured fragment within a URI/URL.
	 *
	 *	@private
	 *	@static
	 *	@param string $url The URL/URI.
	 *	@return string
	 */
	private static function _fix_bad_fragment($url) {
		$parts = explode('#',$url);
		if (count($parts) > 2) {
			$url = array_shift($parts).'#';
			$url = $url.implode('%23',$parts);
		}
		return $url;
	}
	
	/**
	 *	Attempt to fix a badly placed '@' symbol within a URI/URL.
	 *
	 *	@private
	 *	@static
	 *	@param string $url The URL/URI.
	 *	@return string
	 */
	private static function _fix_bad_at($url) {
		$parts = explode('@',$url);
		if (count($parts) > 2) {
			$url = array_shift($parts).'@';
			$url = $url.implode('%40',$parts);
		}
		return $url;
	}
	
	/**
	 *	Does the given URI/URL contain a scheme?
	 *
	 *	@private
	 *	@static
	 *	@param string $url The URL/URI.
	 *	@return boolean
	 */
	private static function _has_scheme($url) {
		$match = preg_match('/\A[A-Za-z0-9]+\:\/\//',$url);
		return (($match==1)?true:false);
	}
	
	/**
	 *	Does the given URI/URL contain a password?
	 *
	 *	@private
	 *	@static
	 *	@param string $url The URL/URI.
	 *	@return boolean
	 */
	private static function _has_password($url) {
		$match = preg_match('/\A[A-Za-z0-9]+\:\/\/[^\/]+\:[^\/]+\@/',$url);
		return (($match==1)?true:false);
	}
	
	/**
	 *	Does the given URI/URL contain a username?
	 *
	 *	@private
	 *	@static
	 *	@param string $url The URL/URI.
	 *	@return boolean
	 */
	private static function _has_username($url) {
		$match = preg_match('/\A[A-Za-z0-9]+\:\/\/[^\/]+\@/',$url);
		return (($match==1)?true:false);
	}
	
	/**
	 *	Does the given URI/URL contain a port?
	 *
	 *	@private
	 *	@static
	 *	@param string $url The URL/URI.
	 *	@return boolean
	 */
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
}
?>