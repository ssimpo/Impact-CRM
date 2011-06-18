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
class Test_Acl_REF extends ImpactPHPUnit {
	
	protected function setUp() {
		$this->init();
	}
	
	public function test_test_keywords() {
		$_SERVER['HTTP_REFERER'] = 'http://www.google.co.uk/search?hl=en&xhr=t&q=churches+in+middlesbrough&cp=16&pf=p&sclient=psy&safe=off&aq=0&aqi=&aql=&oq=churches+in+midd&pbx=1&fp=2d2ccc87393ce188';
		$this->assertTrue(
			$this->instance->test_keywords(array('MIDDLESBROUGH','CHURCHES'))
		);
		$this->assertFalse(
			$this->instance->test_keywords(array('LONDON','CHURCHES'))
		);
	}
}
?>