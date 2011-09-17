<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *	Main database class
 *
 *	A series of methods, which give access to the database.  Query results are
 *	cached for increased speed.  The <a href="http://adodb.sourceforge.net/">ADODB</a>
 *	library is used so that a number of backend databases can be accessed (including
 *	MySQL and SQLite.
 *	
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.5
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Database
 *	@extends Impact_Base
 */
class Database extends Singleton {
	private static $instance;
	private $database=null;
	
	/**
	 *	Test connection.
	 *
	 *	Test if connection is already made, if not, create it.
	 *	
	 *	@private
	 */
	private function _test_connection() {
		if (is_null($this->database)) {
			$this->_make_database_connection();
		}
	}
	
	/**
	 *	Make a database connection.
	 *
	 *	Connect to the database defined in the global constants.
	 *
	 *	@private
	 *	@todo Test with other backends (other than MySQL/SQLite).
	 *	@todo Make generic version so platform can connect to multiple database sources.
	 */
	private function _make_database_connection() {
		$ADODB_CACHE_DIR = ROOT_BACK.CACHE_DIRECTORY;
	
		switch (strtoupper(DB_DRIVER)) {
			case 'SQLITE':
				$this->database = ADONewConnection('pdo');
				$this->database->PConnect(strtolower(DB_DRIVER).':'.ROOT_BACK.DB_NAME);
				break;
			case 'MYSQL':
			default:
				$this->database = ADONewConnection(DB_DRIVER);
				$this->database->PConnect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
				break;
		}
		
		$this->database->debug = false;
	}
	
	public function try_rows($timeout,$SQL,$entities,$values) {
		$sequencer = new Database_SqlSequencer;
		$sequencer->entities = $entities;
		$sequencer->values = $values;
		$sequencer->sql = $SQL;
		
		return $sequencer->exec();
	}
	
	public function try_row($timeout,$SQL,$entities,$values) {
		$sequencer = new Database_SqlSequencer;
		$sequencer->entities = $entities;
		$sequencer->values = $values;
		$sequencer->sql = $SQL;
		
		$rs = $sequencer->exec();
		if (!empty($rs)) {
			return array_shift($rs);
		}
	}
	
	/**
	 *	Get a database row.
	 *
	 *	@public
	 *	@param integer $timeout How long to allow before returning false.
	 *	@param string $SQL The SQL statement to execute.
	 *	@return mixed()|boolean Either the result-row or false on failure.
	 */
	public function get_row($timeout,$SQL) {
		$this->_test_connection();
		$rs = $this->get_rows($timeout,$SQL);
		
		if (!empty($rs)) {
			return array_shift($rs);
		}
		
		return false;
	}
	
	/**
	 *	Get a database rows.
	 *
	 *	@public
	 *	@param integer $timeout How long to allow before returning false.
	 *	@param string $SQL The SQL statement to execute.
	 *	@return mixed()|boolean Either the result-rows or false on failure.
	 */
	public function get_rows($timeout,$SQL) {
		$this->_test_connection();
		$rs = $this->database->CacheSelectLimit($timeout,$SQL);
		if ($rs) {
			return $rs->GetAll();
		} else {
			return false;
		}
	}
	
	/**
	 *	Get the specified page.
	 *
	 *	Specific Impact method for returning page data.
	 *
	 *	@public
	 *	@param integer $entityID The ID of the page to return.
	 *	@return mixed()|boolean Either the result-row or false on failure.
	 */
	public function get_page($entityID='') {
		$this->_test_connection();
		$application = Application::instance();
		$reader_roles = $this->create_roles_sql('readers');
		if ($entityID === '') {
			$entityID = $application->entityID;
		}
		
		$SQL = '
			SELECT entities.application as application, content.*
			FROM content
			INNER JOIN entities ON entities.ID = content.entityID
			WHERE (entities.ID='.$entityID.') AND (current="YES") 
				AND (media LIKE "%'.$application->media.'%")
				AND (content.lang LIKE "%<LANG>%")
		';
		
		$rs = $this->try_row(
			120,$SQL,'<LANG>',
			array(array(strtolower($application->language),strtolower(DEFAULT_LANG)))
		);
		
		$rs['sectionTitle'] = $this->_get_section($entityID);
		
		return $rs;
	}
	
	private function _get_section($entityID) {
		$application = Application::instance();
		$parentPath = $this->_get_parent_structure_path($entityID);
		if ($parentPath == '') {
			return $parentPath;
		}
		
		$SQL = '
			SELECT title
			FROM structure
			WHERE (path="'.$parentPath.'") AND (lang LIKE "%<LANG>%")
		';
		$rs = $this->try_row(
			120,$SQL,'<LANG>',
			array(array(strtolower($application->language),strtolower(DEFAULT_LANG)))
		);
		
		if ($rs) {
			return $rs['title'];
		} else {
			return false;
		}
	}
	
	private function _get_parent_structure_path($entityID) {
		$sectionPath = $this->_get_structure_path($entityID);
		if (!$sectionPath) {
			return '';
		}
		
		$parts = explode('/',$sectionPath);
		$currentPart = array_pop($parts);
		if (empty($parts)) {
			return $currentPart;
		} else {
			return implode('/',$parts);
		}
	}
	
	private function _get_structure_path($entityID) {
		$application = Application::instance();
		
		$SQL = '
			SELECT path
			FROM structure
			WHERE (entityID='.$entityID.') AND (lang LIKE "%<LANG>%")
		';
		$rs = $this->try_row(
			120,$SQL,'<LANG>',
			array(array(strtolower($application->language),strtolower(DEFAULT_LANG)))
		);
		
		if ($rs) {
			return $rs['path'];
		} else {
			return false;
		}
	}
	
	/**
	 *	Get the specified menu.
	 *
	 *	Specific Impact method for returning menu data.
	 *
	 *	@public
	 *	@param string $menu The name of the menu to return
	 *	@return mixed()|boolean Either the result-rows or false on failure.
	 */
	public function get_menu($menu,$startLevel=0,$endLevel=10000) {
		$application = Application::instance();
		$reader_roles = $this->create_roles_sql('readers');
		
		$SQL = '
			SELECT
				entities.title AS path,structure.title AS title,
				structure.path as structure, structure.sequence as sequence
			FROM structure
			INNER JOIN entities ON structure.entityID = entities.ID
			WHERE menu="'.$menu.'" AND include="YES"
			AND lang LIKE "%<LANG>%"
			ORDER BY sequence
		';
		
		$rows = $this->try_rows(
			120,$SQL,'<LANG>',
			array(strtolower($application->language),strtolower(DEFAULT_LANG))
		);
		
		$menu = array();
		$lookup = array();
		if ($rows) {
			foreach ($rows as $row) {
				$row['children'] = array();
				$pathParts = I::array_trim(explode('/',$row['structure']));
				array_pop($pathParts);
				$level = count($pathParts);
				$parentPath = implode('/',$pathParts);
				
				if (($level >= $startLevel) && ($level <= $endLevel)) {
					$cmenu = &$menu;
					
					if ($parentPath != '') {
						if (array_key_exists($parentPath,$lookup)) {
							$cmenu = &$lookup[$parentPath]['children'];
						}
					}
					$count = array_push($cmenu,$row);
					$lookup[$row['structure']] = &$cmenu[$count-1];
					
				}
			}
			
			return $menu;
		} else {
			return $rows;
		}
	}
	
	/**
	 *	Get the users access roles.
	 *
	 *	Specific Impact method for returning the Acl-roles, which the supplied
	 *	user is a member.
	 *
	 *	@public
	 *	@param integer $fbid The Facebook ID of the user to query.
	 *	@return string() Array of all the rules that the user is in
	 */
	public function get_roles($fbid=0) {
		$roles = $this->get_row(
			DEFAULT_CACHE_TIMEOUT,
			'SELECT roles,access FROM users WHERE fbid='.$fbid
		);
		return explode(',',I::reformat_role_string($roles['roles']));
	}
	
	/**
	 *	Get the users access level.
	 *
	 *	Specific Impact method for returning the Acl access level of the
	 *	supplied user.
	 *
	 *	@public
	 *	@param integer $fbid The Facebook ID of the user to query.
	 *	@return integer Access level of the supplied user.
	 */
	public function get_access($fbid=0) {
		$access = $this->get_row(
			DEFAULT_CACHE_TIMEOUT,
			'SELECT access FROM users WHERE fbid='.$fbid
		);
		return $access['access'];
	}
	
	/**
	 *	Generate SQL fragment for testing against certain roles.
	 *
	 *	Create a fragment of SQL, which can be used in a wider SQL statement
	 *	to restrict results to items, which a certain user has access rights to.
	 *
	 *	@public
	 *	@param string $field The field to test against
	 *	@param string() $roles The roles to test against (default will grab the current users roles).
	 *	@return string SQL fragment based on field and roles
	 */
	public function create_roles_sql($field,$roles='') {
		$application = Application::instance();
		if ($roles == '') {
			$roles = $application->roles;
		}
		
		$SQL = '';
		foreach ($roles as $role) {
			if ($SQL != '') {
				$SQL .= ' OR ';
			}
			$SQL .= '('.$field.' LIKE "%'.$role.'%")';
		}
		return '('.$SQL.')';
	}
	
	
}