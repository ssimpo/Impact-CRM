<?php
require_once('globals.php');

/**
 *	Unit Test for the Acl class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends ImpactPHPUnit
 */
class Test_Acl extends ImpactPHPUnit {
	
	protected function setUp() {
        $this->init('Acl');
		$application = Application::instance();
	}
	
	public function test_load_roles() {
		$this->instance->load_roles('[WEB][ADMIN][DEV]');
		$method = self::get_method('allowed');
		
		$this->assertFalse(
			$method->invokeArgs($this->instance, array('[WEB2]'))
		);
		
		$this->instance->load_roles('[WEB2]');
		$this->assertTrue(
			$method->invokeArgs($this->instance, array('[WEB2]'))
		);
	}
	
	public function test_allowed() {
		$this->instance->load_roles('[WEB][ADMIN][DEV]');
		$method = self::get_method('allowed');
		
		$this->assertTrue(
			$method->invokeArgs($this->instance, array('[WEB][FB:USER:93][DEVELOPER]','[WEB2]'))
		);
		$this->assertFalse(
			$method->invokeArgs($this->instance, array('[WEB],[FB:USER:93],[DEVELOPER]','[DEV]'))
		);
		$this->assertTrue(
			$method->invokeArgs($this->instance, array('[WEB]'))
		);
		$this->assertFalse(
			$method->invokeArgs($this->instance, array('','[WEB]'))
		);
		$this->assertFalse(
			$method->invokeArgs($this->instance, array('[DEV],[ADMIN]','[WEB]'))
		);
		$this->assertFalse(
			$method->invokeArgs($this->instance, array('[WEB2]','[WEB][ADMIN][WEB3]'))
		);
	}
	
	public function test_test_role() {
		$this->instance->load_roles('[WEB][ADMIN][DEV]');
		$method = self::get_method('test_role');
		
		/*$this->assertTrue(
			$method->invokeArgs($this->instance, array('[WEB][FB:USER:93][DEVELOPER]'))
		);
		$this->assertFalse(
			$method->invokeArgs($this->instance, array('[WEB2][FB:USER:93][DEVELOPER]'))
		);*/
	}
	
	public function test_split_special_role() {
		$method = self::get_method('_split_special_role');
		
		/*$this->assertEquals(
			array('FB','USER',array('93')),
			$method->invokeArgs($this->instance, array('[FB:USER:93]'))
		);
		$this->assertEquals(
			array('GEO','RADIUS',array('39.0335','-78.4838','90','KM')),
			$method->invokeArgs($this->instance, array('[GEO:RADIUS:39.0335:-78.4838:90:KM]'))
		);*/
	}
}
?>