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
class Test_Acl_GEO extends ImpactPHPUnit {
	
	protected function setUp() {
		$this->init('Acl_GEO');
	}
	
	public function test_test_city() {
		$_SERVER['REMOTE_ADDR'] = '166.56.23.1';
		$this->assertTrue(
			$this->instance->test_city(array('ASHBURN'))
		);
	}
	
	public function test_test_country() {
		$_SERVER['REMOTE_ADDR'] = '166.56.23.1';
		$this->assertTrue(
			$this->instance->test_country(array('US'))
		);
	}
	
	public function test_test_region() {
		$_SERVER['REMOTE_ADDR'] = '166.56.23.1';
		$this->assertTrue(
			$this->instance->test_region(array('VA'))
		);
	}
	
	public function test_test_radius() {
		$_SERVER['REMOTE_ADDR'] = '166.56.23.1';
		$this->assertTrue(
			$this->instance->test_radius(array('39.0335','-78.4838','90','KM'))
		);
		$this->assertTrue(
			$this->instance->test_radius(array('39.0335','-78.4838','56','M'))
		);
	}
}
?>