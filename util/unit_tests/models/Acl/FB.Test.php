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
class Test_Acl_FB extends ImpactPHPUnit {
	const APP_ID = '117743971608120';
	const SECRET = '943716006e74d9b9283d4d5d8ab93204';
	
	private static $VALID_EXPIRED_SESSION = array(
		'access_token' => '117743971608120|2.vdCKd4ZIEJlHwwtrkilgKQ__.86400.1281049200-1677846385|NF_2DDNxFBznj2CuwiwabHhTAHc.',
		'expires' => '1281049200',
		'secret' => 'u0QiRGAwaPCyQ7JE_hiz1w__',
		'session_key' => '2.vdCKd4ZIEJlHwwtrkilgKQ__.86400.1281049200-1677846385',
		'sig' => '7a9b063de0bef334637832166948dcad',
		'uid' => '1677846385',
	);

	private $application = null;
	
	protected function setUp() {
		$this->application = Application::instance();
		$this->init('Acl_FB',$this->application);
		
		$this->application->facebook = new Facebook(array(
			'appId'  => self::APP_ID,
			'secret' => self::SECRET,
		));
		$this->instance->facebook = $this->application->facebook;
	}
	
	public function test_test_user() {
		$session = self::$VALID_EXPIRED_SESSION;
		$this->application->facebook->setSession($session);
		$this->assertTrue(
			$this->instance->test_user(array('1677846385'))
		);
	
		$this->application->FBID = 2;
		$this->assertTrue(
			$this->instance->test_user(array('2'))
		);
		$this->assertFalse(
			$this->instance->test_user(array('3'))
		);
		$this->assertTrue(
			$this->instance->test_user(array(2))
		);
	}
	
	public function test_test_friend() {
		// STUB
	}
}
?>