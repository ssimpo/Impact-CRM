<?php
/**
 *	Unit Test for the Database class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends PHPUnit_Framework_TestCase
 */
class Test_Database extends PHPUnit_Framework_TestCase {
    private $database = null;
    
    protected function setUp() {
		if (!defined('__DIR__')) {
			$iPos = strrpos(__FILE__, "/");
			define('__DIR__', substr(__FILE__, 0, $iPos) . '/');
		}
		if (!defined('DS')) {
			define('DS',DIRECTORY_SEPARATOR);
		}
		if (!defined('MODELS_DIRECTORY')) {
			define('MODELS_DIRECTORY','models');
		}
		if (!defined('ROOT_BACK')) {
			define('ROOT_BACK',__DIR__.DS.'..'.DS.'..'.DS.'..'.DS);
		}
		if (!defined('DB_DRIVER')) {
			define('DB_DRIVER','SQLITE');
		}
		if (!defined('CACHE_DIRECTORY')) {
			define('CACHE_DIRECTORY','cache/');
		}
		if (!defined('DB_NAME')) {
			define('DB_NAME','database/impact.db#');
		}
		spl_autoload_register('self::__autoload');
	
		$this->database = Database::instance();
    }
    
    private function __autoload($className) {
		$classFileName = str_replace('_',DS,$className).'.php';
		require_once ROOT_BACK.MODELS_DIRECTORY.DS.$classFileName;
    }
	
	protected static function get_method($name) {
		$class = new ReflectionClass('Database');
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
    }
    
    public function test_get_row() {
        /*$this->assertEquals(
	    $this->database->get_row(0,'SELECT * FROM _titles WHERE ID=1'),
	    array('ID'=>1,'title'=>'Mr')
	);*/
    }
    
    public function test_get_page() {
        // STUB
    }
    
    public function test_get_roles() {
        // STUB
    }
    
    public function test_get_access() {
        // STUB
    }
    
    public function test_create_roles_sql() {
        // STUB
    }
    
    public function test_make_database_connection() {
        // STUB
    }
}