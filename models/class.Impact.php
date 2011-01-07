<?php
/**
*		Main impact class
*		Currently contains a vast amount of functions and different concepts.  Will need breaking down into seperate classes for different aspects of the platform. Eg. Seperate Database and Facebook classes?
*		
*		@Author: Stephen Simpson
*		@Version: 0.0.1
*		@License: LGPL
*		
*/
class Impact Extends Impact_Superclass {
	private static $instance;
	public $application = array();
	public $ACL;
	public $facebook;
	public $fbsession;
	public $me;
	
	private function __construct() {
		
    }
	
	public function setup() {
		$this->application[FBID] = 0;
		$this->_load_constants();
		$this->_make_facebook_connection();
		$this->_languageDetect();
		$this->_mediaDetect();
		$this->_userAccessDetect();
		
		$this->pageName = strtolower(addslashes($_GET[page]));
		if ($this->pageName == '') {$this->pageName = DEFAULT_HOMEPAGE;}
		$this->pageErrorCheck = $this->_getPageRequestInfo();
	}
	
	private function _load_constants() {
		$this->load_config(get_include_directory().'/../config/settings.xml');
	}
	
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

	public static function singleton() {
        if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
        }
        return self::$instance;
    }
	
	public function __clone() { trigger_error('Clone is not allowed.', E_USER_ERROR);}
	public function __set($property,$value) { $this->application[$property] = $value; }
	public function __get($property) {
		if (array_key_exists($property,$this->application)) {
			return $this->application[$property];
		} else {
			return false;
		}
	}
	
	private function _userAccessDetect () {
		$database = Database::singleton();
		$this->roles = $database->getRoles($this->FBID);
		$this->accessLevel = $database->getAccess($this->FBID);
		
		#This needs a better implimentation but will do to get us going
		$this->ACL = $this->factory('ACL');
		$this->ACL->FBID = $this->application[FBID];
		$this->ACL->accesslevel = $this->application[accessLevel];
		$this->ACL->facebook = $this->facebook;
		$this->ACL->load_roles($this->application[roles]);
		$this->application[ACL] = $this->ACL;
	}
	
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