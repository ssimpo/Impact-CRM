<?php
require_once('globals.php');

/**
 *	  Unit Test for the Acl class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends PHPUnit_Framework_TestCase
 */
class Test_Acl_AGENT extends ImpactPHPUnit {
	
	protected function setUp() {
		$this->init('Acl_AGENT');
	}
	
	public function test_test_browser() {
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10.5; en-US; rv:1.9.0.3) Gecko/2008092414 Firefox/3.0.3';
		$this->assertTrue(
			$this->instance->test_browser(array('FIREFOX'))
		);
	}
	
	public function test_test_platform() {
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10.5; en-US; rv:1.9.0.3) Gecko/2008092414 Firefox/3.0.3';
		$this->assertTrue(
			$this->instance->test_platform(array('MACOSX'))
		);
	}
	
	public function test_test_mobile() {
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10.5; en-US; rv:1.9.0.3) Gecko/2008092414 Firefox/3.0.3';
		$this->assertFalse(
			$this->instance->test_mobile(array())
		);
		
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Linux; U; Android 0.5; en-us) AppleWebKit/522+ (KHTML, like Gecko) Safari/419.3';
		$this->assertTrue(
			$this->instance->test_mobile(array())
		);
	}
}
?>