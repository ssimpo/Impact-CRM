<?php
/**
*		Main database class
*		
*		@Author: Stephen Simpson
*		@Version: 0.0.1
*		@License: LGPL
*		
*/
class Database Extends Impact_Superclass {
	private static $instance;
	private $database;
	
	private function __construct() {
		$this->_make_database_connection();
	}
	
	public static function singleton() {
        if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
        }

        return self::$instance;
    }
	
	public function __clone() { trigger_error('Clone is not allowed.', E_USER_ERROR);}
	
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
		$impact = Impact::singleton();
		$reader_roles = $this->_create_roles_SQL('readers');
		if ($entityID === '') { $entityID = $impact->entityID; }
		
		$SQL = '
			SELECT entities.application as application, content.* FROM content
			INNER JOIN entities ON entities.ID = content.entityID
			WHERE (entities.ID='.$entityID.') AND (current="YES") 
				AND (media LIKE "%'.$impact->media.'%") AND (content.lang 
		';
		
		$rc = $this->getRow(DEFAULT_CACHE_TIMEOUT,$SQL.'LIKE "%'.strtolower($impact->language).'%")');
		if ($rs === false) {
			$SQL_p2 = '"'.strtolower(DEFAULT_LANG).'"';
			$rc = $this->getRow(DEFAULT_CACHE_TIMEOUT,$SQL.'LIKE "%'.strtolower(DEFAULT_LANG).'%")');
		}

		return $rc;
	}
	
	public function getRoles($FBID=0) {
		$roles = $this->getRow(
			DEFAULT_CACHE_TIMEOUT,
			'SELECT roles,access FROM users WHERE FBID='.$FBID
		);
		return explode(',',$this->_add_square_brakets($roles[roles]));
	}
	
	public function getAccess($FBID=0) {
		$access = $this->getRow(
			DEFAULT_CACHE_TIMEOUT,
			'SELECT access FROM users WHERE FBID='.$FBID
		);
		return $access[access];
	}
	
	public function _create_roles_SQL($field,$roles='') {
		$impact = Impact::singleton();
		if ($roles == '') { $roles = $impact->roles; }
		
		$SQL = '';
		foreach ($roles as $role) {
			if ($SQL != '') { $SQL .= ' OR '; }
			$SQL .= '('.$field.' LIKE "%'.$role.'%")';
		}
		return '('.$SQL.')';
	}
	
	
}