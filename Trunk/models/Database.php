<?php
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
		require_once ROOT_BACK.'includes/adodb/adodb.inc.php';
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
		$rs = $this->database->CacheSelectLimit($timeout,$SQL,1);
		if ($rs) {
			$rs = $rs->GetAll();
			return $rs[0];
		} else {
			return false;
		}
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
			$rs = $rs->GetAll();
			return $rs;
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
			SELECT entities.application as application, content.* FROM content
			INNER JOIN entities ON entities.ID = content.entityID
			WHERE (entities.ID='.$entityID.') AND (current="YES") 
				AND (media LIKE "%'.$application->media.'%") AND (content.lang 
		';
		
		$rs = $this->get_row(DEFAULT_CACHE_TIMEOUT,$SQL.'LIKE "%'.strtolower($application->language).'%")');
		if ($rs === false) {
			$SQL_p2 = '"'.strtolower(DEFAULT_LANG).'"';
			$rc = $this->get_row(DEFAULT_CACHE_TIMEOUT,$SQL.'LIKE "%'.strtolower(DEFAULT_LANG).'%")');
		}

		return $rs;
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
	public function get_menu($menu,$startLevel=0,$endLevel=1000) {
		$application = Application::instance();
		$reader_roles = $this->create_roles_sql('readers');
		
		$SQL = '
			SELECT entities.title AS path,structure.title AS title,sequence
			FROM structure
			INNER JOIN entities ON structure.entityID = entities.ID
			WHERE menu="'.$menu.'"
			AND include="YES"
			AND lang LIKE "%'.strtolower($application->language).'%"
			ORDER BY sequence
		';
		
		$menu = array();
		$rows = $this->get_rows(120,$SQL);
		if ($rows) {
			foreach ($rows as $row) {
				$level = strlen($row['sequence'])-1;
				if (($level >= $startLevel) && ($level <= $endLevel)) {
					array_push($menu,$row);
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