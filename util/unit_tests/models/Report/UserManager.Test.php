<?php
require_once('globals.php');

/**
 *	Unit Test for the Report_UserManager class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends ImpactPHPUnit
 */
class Test_Report_UserManager extends ImpactPHPUnit {
	
	protected function setUp() {
		$this->init();
	}
	
	public function test_reset() {
		// STUB
	}
	
	public function test_close_all_sessions() {
		// STUB
	}
	
	public function test_get_user() {
		// STUB
	}
	
	public function test_attach_event() {
		// STUB
	}
	
	public function test_remove_event() {
		// STUB
	}
	
	public function test_clear_event() {
		// STUB
	}
	
	public function test_clear_all_events() {
		// STUB
	}
	
	public function test_search_callers() {
		// STUB
	}
	
	public function test_create_caller() {
		// STUB
	}
	
	public function test_on_new_session() {
		// STUB
	}
	
	public function test_on_end_session() {
		// STUB
	}
	
	public function test_on_new_user() {
		// STUB
	}
	
	public function test_call_user_func_array() {
		// STUB
	}
	
	public function test_convert_to_arguments_array() {
		$this->assertMethodReturn( array(1,2), array(array(1,2)) );
		$this->assertMethodReturn( array(1), 1 );
		$this->assertMethodReturn( array('one','two','three'), array(array('one','two','three')) );
	}
	
	public function test_get_hash() {
		$this->assertMethodReturnType('string','testtest');
		$return = $this->getMethodReturn('testtest');
		$this->assertEquals(32,strlen($return));
		$this->assertTrue(is_numeric('0x'.$return));
	}
	
	public function test_get_user_id() {
		// STUB
	}
	
	public function test_get_google_analytics_user_id() {
		// STUB
	}
	
	public function test_is_numeric_indexed_array() {
		$array = array(1,2,3,3);
		$this->assertMethodReturnTrue(array($array));
		
		$array = array(1,'two'=>2,3,3);
		$this->assertMethodReturnFalse(array($array));
		
		$array = array('one'=>1,'two'=>2,'three'=>3);
		$this->assertMethodReturnFalse(array($array));
		
		$array = array(1,array(1,2,3),3);
		$this->assertMethodReturnTrue(array($array));
	}
}
?>