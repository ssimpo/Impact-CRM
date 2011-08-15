<?php
require_once('globals.php');

/**
 *	Unit Test for the Report_UserManager_User class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends ImpactPHPUnit
 */
class Test_Report_UserManager_User extends ImpactPHPUnit {
	
	protected function setUp() {
		$this->init();
	}
	
	public function test_reset() {
		// STUB
	}
	
	public function test_parse() {
		// STUB
	}
	
	public function test_handle_first_request() {
		// STUB
	}
	
	public function test_create_new_session() {
		// STUB
	}
	
	public function test_get_now() {
		// STUB
	}
	
	public function test_on_new_session() {
		// STUB
	}
	
	public function test_on_end_session() {
		// STUB
	}
	
	public function test_get_uri() {
		// STUB
	}
	
	public function test_call_user_func_array() {
		// STUB
	}
	
	public function test_get_hash() {
		// STUB
	}
	
	public function test_convert_to_arguments_array() {
		// STUB
	}
	
	public function test_is_numeric_indexed_array() {
		$array = array(1,2,3,3);
		$this->assertMethodReturnTrue(array($array));
		
		/*$array = array(1,'two'=>2,3,3);
		$this->assertMethodReturnFalse(array($array));
		
		$array = array('one'=>1,'two'=>2,'three'=>3);
		$this->assertMethodReturnFalse(array($array));
		
		$array = array(1,array(1,2,3),3);
		$this->assertMethodReturnFalse(array($array));8?*/
	}
}
?>