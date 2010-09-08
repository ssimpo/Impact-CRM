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
class Impact {
	private static $instance;
	public $database;
	public $application = array();
	public $ACL;
	public $facebook;
	public $fbsession;
	public $me;
	
	private function __construct() {
		$this->application[FBID] = 0;
		$this->_load_constants();
		$this->_make_database_connection();
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
	
	private function _make_database_connection() {
		require_once($this->get_include_directory().'/adodb/adodb.inc.php');
		$ADODB_CACHE_DIR = ROOT_BACK.'/'.CACHE_DIRECTORY;
		
		switch (strtoupper(DB_DRIVER)) {
			case 'SQLITE':
				$this->database = ADONewConnection('pdo');
				$this->database->PConnect(strtolower(DB_DRIVER).':'.DB_NAME);
				break;
			case 'MYSQL':
			default:
				$this->database = ADONewConnection(DB_DRIVER);
				$this->database->PConnect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
				break;
		}
		
		
		$this->database->debug = false; 
		
		#$this->database->PConnect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
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
	
	public static function factory($className) {
		$dir = self::get_include_directory();
		
        if (include_once $dir.'/class.'.str_replace('_','.',$className).'.php') {
            return new $className;
        } else {
            throw new Exception($className.' Class not found');
        }
    }
	
	private function _userAccessDetect () {
		$roles = $this->getRow(
			DEFAULT_CACHE_TIMEOUT,
			'SELECT roles,access FROM users WHERE FBID='.$this->application[FBID]
		);
		$this->application[roles] = explode(',',$roles[roles]);
		$this->application[accessLevel] = $roles[access];
		
		#This needs a better implimentation but will do to get us going
		$this->ACL = $this->factory('ACL');
		$this->ACL->FBID = $this->application[FBID];
		$this->ACL->accesslevel = $this->application[accessLevel];
		$this->ACL->facebook = $this->facebook;
		$this->ACL->load_roles($this->application[roles]);
		$this->application[ACL] = $this->ACL;
	}
	
	private function _create_roles_SQL($field,$roles='') {
		if ($roles == '') { $roles = $this->roles; }
		
		$SQL = '';
		foreach ($roles as $role) {
			if ($SQL != '') { $SQL .= ' OR '; }
			$SQL .= '('.$field.' LIKE "%'.$role.'%")';
		}
		return '('.$SQL.')';
	}
	
	public function getRow($timeout,$SQL) {
		$rs = $this->database->CacheSelectLimit($timeout,$SQL,1);
		if ($rs) {
			$rs = $rs->GetAll();
			return $rs[0];
		} else {
			return false;
		}
	}
	
	public function getPage($entityID='') {
		$reader_roles = $this->_create_roles_SQL('readers');
		if ($entityID === '') { $entityID = $this->entityID; }
		
		$SQL = '
			SELECT entities.application as application, content.* FROM content
			INNER JOIN entities ON entities.ID = content.entityID
			WHERE (entities.ID='.$entityID.') AND (current="YES") 
				AND (media LIKE "%'.$this->media.'%") AND content.lang=
		';
		
		$rc = $this->getRow(DEFAULT_CACHE_TIMEOUT,$SQL.'"'.strtolower($this->language).'"');
		if ($rs === false) {
			$SQL_p2 = '"'.strtolower(DEFAULT_LANG).'"';
			$rc = $this->getRow(DEFAULT_CACHE_TIMEOUT,$SQL.'"'.strtolower(DEFAULT_LANG).'"');
		}

		return $rc;
	}
	
	function _getPageRequestInfo() {
		$this->entityID = 0;
		$errorcheck = false;
		$reader_roles = $this->_create_roles_SQL('readers');
	
		if (is_numeric($this->pageName)) {
			$errorcheck = $this->getRow(
				DEFAULT_CACHE_TIMEOUT,
				'SELECT Title FROM entities WHERE (ID='.$this->pageName.') AND '.$reader_roles
			);
			if ($errorcheck) {
				$this->entityID = $application[pageName];
				$this->pageName = $errorcheck['Title'];
			} 
		} else {
			$errorcheck = $this->getRow(
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
	
		$this->application[media] = $media;
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
	
		$this->application[language] = $lang;
	}
	
	public function get_include_directory() {
		$debug = debug_backtrace();
		return dirname($debug[0][file]);
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