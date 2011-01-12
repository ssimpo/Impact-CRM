<?php
/**
 *	Main impact class
 *	
 *	Class containing a vast amount of functions and different concepts.
 *	Will need breaking down into seperate classes for different aspects of the
 *	platform. Eg. Seperate Database and Facebook classes?
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.4
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 
 *	@todo Breakdown into seperate classes for different areas
 *	@package Impact
 *	@extends Impact_Base
 */
class Application Extends Impact_Base {
	private static $instance;
	public $settings = array();
	public $ACL;
	public $facebook;
	public $fbsession;
	public $me;
	
	/**
	 *	Main constructor.
	 *
	 *	@private
	 *	@deprecated
	 */
	private function __construct() {
	}
	
	/**
	 *	Singleton method.
	 *
	 *	Provide a reference to the one static instance of this class.  Stops
	 *	class being declared muliple times.
	 *
	 *	@public
	 *	@static
	 *
	 *	@return Impact
	 *	
	 */
	public static function singleton() {
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}
	
	/**
	 *	Intitization method.
	 *
	 *	@public
	 */
	public function setup() {
		$this->settings[FBID] = 0;
		$this->_load_constants();
		$this->_make_facebook_connection();
		$this->_languageDetect();
		$this->_mediaDetect();
		$this->_userAccessDetect();
		
		$this->pageName = strtolower(addslashes($_GET[page]));
		if ($this->pageName == '') {$this->pageName = DEFAULT_HOMEPAGE;}
		$this->pageErrorCheck = $this->_getPageRequestInfo();
	}
	
	/**
	 *	Load constants from an XML file.
	 *
	 *	Loads a series of constants from a settings file (XML).  Values
	 *	are loaded into the global scope.
	 *
	 *	@private.
	 *	@todo Make generic so that settings can be loaded from anywhere?
	 */
	private function _load_constants() {
		$this->load_config(I::get_include_directory().'/../config/settings.xml');
	}
	
	/**
	 *	Make a Facebook connection.
	 *
	 *	Connect to Facebook and return the session and Facebook objects
	 *	to Impact properties (facebook and fbsession).
	 *
	 *	@public
	 *	@todo Update to the newest Facebook API.
	 */
	function _make_facebook_connection() {
		$this->facebook = new Facebook(array(
			'appId'  => FB_APPKEY,
			'secret' => FB_SECRET,
			'cookie' => true,
		));
		$this->fbsession = $this->facebook->getSession();
	
		if ($this->fbsession) {
			try {
				$this->uid = $this->facebook->getUser();
				$this->me = $this->facebook->api('/me');
			} catch (FacebookApiException $e) {
				error_log($e);
			}
		}
	}
	
	/**
	 *	Cloning method.
	 *
	 *	This returns an error, since cloning of a singleton is not allowed.
	 *
	 *	@public
	 */
	public function __clone() {
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}
	
	/**
	 *	Generic set property method.
	 *
	 *	Set the value of an application property.  Values are stored in
	 *	the application array and accessed via the __set and __get methods.
	 *
	 *	@public
	 */
	public function __set($property,$value) {
		$this->settings[$property] = $value;
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
		if (array_key_exists($property,$this->settings)) {
			return $this->settings[$property];
		} else {
			return false;
		}
	}
	
	/**
	 *	Set the current users access levels.
	 *
	 *	These are calculated from data stored in the database.
	 *
	 *	@private
	 *	@todo Needs a bit of work to improve it but works well and dosen't have any major security flaws.
	 */
	private function _userAccessDetect () {
		$database = Database::singleton();
		$this->roles = $database->getRoles($this->FBID);
		$this->accessLevel = $database->getAccess($this->FBID);
		
		//This needs a better implimentation but will do to get us going
		$this->ACL = $this->factory('ACL');
		$this->ACL->FBID = $this->settings[FBID];
		$this->ACL->accesslevel = $this->settings[accessLevel];
		$this->ACL->facebook = $this->facebook;
		$this->ACL->load_roles($this->settings[roles]);
		$this->settings[ACL] = $this->ACL;
	}
	
	/**
	 *	Check if current page request is valid.
	 *
	 *	Does the requested page exist? Does the current user have access.
	 *
	 *	@public
	 *	@return boolean Is the request valid.
	 */
	function _getPageRequestInfo() {
		$this->entityID = 0;
		$errorcheck = false;
		$database = Database::singleton();
		
		$reader_roles = $database->_create_roles_SQL('readers');
	
		if (is_numeric($this->pageName)) {
			$errorcheck = $database->getRow(
				DEFAULT_CACHE_TIMEOUT,
				'SELECT Title FROM entities WHERE (ID='.$this->pageName.') AND '.$reader_roles
			);
			if ($errorcheck) {
				$this->entityID = $application[pageName];
				$this->pageName = $errorcheck['Title'];
			} 
		} else {
			$errorcheck = $database->getRow(
				DEFAULT_CACHE_TIMEOUT,
				'SELECT ID FROM entities WHERE (Title="'.$this->pageName.'") AND '.$reader_roles
			);
			if ($errorcheck) {
				$this->entityID = $errorcheck['ID'];
			} 
		}
	
		return ($errorcheck)?true:false;
	}
	
	/**
	 *	Detect the media being used.
	 *
	 *	Will detect the media being used (eg. Desktop PC, Mobile, iPad,
	 *	Facebook, ...etc). Data is returned to the media property.
	 *	
	 *	@protected
	 *	@todo Add detection for wider range of media.
	 */
	protected function _mediaDetect() {
		$media = DEFAULT_MEDIA;
		if (isset($_GET[media])) {
			$media = strtoupper(addslashes($_GET[media]));
		} else {
			#Auto-detection of Robots and FB needed :)
			if (substr(DOMAIN,0,2) == 'm.') {#Accessed the mobile subdomain
				$media = 'MOBILE';
			} elseif ($application[browser]->isMobileDevice) {
				$media = 'MOBILE';
			}
		}
		
		$this->media = $this->_add_square_brakets($media);
	}
	
	/**
	 *	Dectect the language being used.
	 *
	 *	What is the native language of the current user?  Is detected from
	 *	the browser request headers and query-string.  Data is returned to
	 *	the language property.
	 *
	 *	@protected
	 *	@todo Allow Facebook language detection and user database lookup (for stored setting).
	 */
	protected function _languageDetect() {
		$lang = DEFAULT_LANG;
		if (isset($_GET[lang])) {
			$lang = strtolower(addslashes($_GET[lang]));
		} else {
			if (isset($_SERVER[HTTP_ACCEPT_LANGUAGE])) {#Auto detection of first language
				$lang = explode(',',$_SERVER[HTTP_ACCEPT_LANGUAGE]);
				$lang = $lang[0];
				$lang = str_replace('-','_',$lang);
			}
		}
		
		$this->language = $this->_add_square_brakets($lang);
	}
	
	/**
	 *	Load a config file
	 *
	 *	Loads a config file (XML) and returns it's values as an array. String-values
	 *	are returned as strings and integer-values as intergers.
	 *
	 *	@publiic
	 *	@param String $path Location of the settings file.
	 *	@return string()|interger()
	 *	@todo Make it work with more complex data types.
	 */
	public function load_config($path) {
		$config = simplexml_load_file($path);
	
		foreach ($config->param as $param) {
				switch ($param['type']) {
				case 'string':
					define($param['name'],$param['value']);
					break;
				case 'integer':
					define($param['name'],(int) $param['value']);
					break;
			}
		}
	}
}
?>