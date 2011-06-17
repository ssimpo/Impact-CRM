<?php
require_once('globals.php');

/**
 *	Unit Test for the Database class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends ImpactPHPUnit
 */
class Test_Database extends ImpactPHPUnit {
	
    protected function setUp() {
		if (!defined('DB_DRIVER')) {
			define('DB_DRIVER','SQLITE');
		}
		if (!defined('CACHE_DIRECTORY')) {
			define('CACHE_DIRECTORY','cache/');
		}
		if (!defined('DB_NAME')) {
			define('DB_NAME','database/impact.sqlite');
		}
	
		$this->init('Database');
    }
    
    public function test_get_row() {
        /*$this->assertEquals(
	    $this->instance->get_row(0,'SELECT * FROM _titles WHERE ID=1'),
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