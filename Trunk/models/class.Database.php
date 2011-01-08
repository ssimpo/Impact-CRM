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
*		
*/
class Database Extends Impact_Superclass {
	private static $instance;
	private $database;
	
	/**
	 *	Constructor method.
	 *
	 *	Method is private, since it is meant to be used as a static-singlton.
	 *	
	 *	@access private
	 */
	private function __construct() {
		$this->_make_database_connection();
	}
	
	/**
	 *	Singleton method.
	 *
	 *	Provide a reference to the one static instance of this class.  Stops
	 *	class being declared muliple times.
	 *
	 *	@access public
	 *	@static
	 *
	 *	@return Database
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
	 *	Cloning method.
	 *
	 *	This returns an error, since cloning of a singleton is not allowed.
	 *
	 *	@access public
	 */
	public function __clone() {
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}
	
	/**
	 *	Make a database connection.
	 *
	 *	Connect to the database defined in the global constants.
	 *
	 *	@access private
	 *	@todo Test with other backends (other than MySQL/SQLite).
	 *	@todo Make generic version so platform can connect to muliple database sources.
	 */
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
	
	/**
	 *	Get a database row.
	 *
	 *	@access public
	 *	@param integer $timeout How long to allow before returning false.
	 *	@param string $SQL The SQL statement to execute.
	 *	@return mixed()/boolean Either the result-row or false on failure.
	 */
	public function getRow($timeout,$SQL) {
		$rs = $this->database->CacheSelectLimit($timeout,$SQL,1);
		if ($rs) {
			$rs = $rs->GetAll();
			return $rs[0];
		} else {
			return false;
		}
	}
	
	/**
	 *	Get the specified page.
	 *
	 *	Specific Impact method for returning page data.
	 *
	 *	@access public
	 *	@param integer $entityID The ID of the page to return.
	 *	@return mixed()/boolean Either the result-row or false on failure.
	 */
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
	
	/**
	 *	Get the users access roles.
	 *
	 *	Specific Impact method for returning the ACL-roles, which the supplied
	 *	user is a member.
	 *
	 *	@access public
	 *	@param integer $FBID The Facebook ID of the user to query.
	 *	@return string() Array of all the rules that the user is in
	 */
	public function getRoles($FBID=0) {
		$roles = $this->getRow(
			DEFAULT_CACHE_TIMEOUT,
			'SELECT roles,access FROM users WHERE FBID='.$FBID
		);
		return explode(',',$this->_add_square_brakets($roles[roles]));
	}
	
	/**
	 *	Get the users access level.
	 *
	 *	Specific Impact method for returning the ACL access level of the
	 *	supplied user.
	 *
	 *	@access public
	 *	@param interger $FBID The Facebook ID of the user to query.
	 *	@return interger Access level of the supplied user.
	 *	
	 */
	public function getAccess($FBID=0) {
		$access = $this->getRow(
			DEFAULT_CACHE_TIMEOUT,
			'SELECT access FROM users WHERE FBID='.$FBID
		);
		return $access[access];
	}
	
	/**
	 *	Generate SQL fragment for testing against certain roles.
	 *
	 *	Create a fagment of SQL, which can be used in a wider SQL statement
	 *	to restrict results to items, which a certain user has access rights to.
	 *
	 *	@access public
	 *	@param string $field The field to test against
	 *	@param string() $roles The roles to test against (default will grab the current users roles).
	 *	@return string SQL fragment based on field and roles
	 */
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