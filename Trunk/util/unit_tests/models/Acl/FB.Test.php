<?php
/**
 *	  Unit Test for the Acl class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends PHPUnit_Framework_TestCase
 */
class Test_Acl_FB extends PHPUnit_Framework_TestCase {
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
  
	private $Acl;
	private $application = null;
	
	protected function setUp() {
		if (!defined('DS')) {
			define('DS',DIRECTORY_SEPARATOR);
		}
		if (!defined('MODELS_DIRECTORY')) {
			define('MODELS_DIRECTORY','models');
		}
		if (!defined('ROOT_BACK')) {
			define('ROOT_BACK',__DIR__.DS.'..'.DS.'..'.DS.'..'.DS.'..'.DS);
		}
		spl_autoload_register('self::__autoload');
		
		$this->application = Application::instance();
		$this->Acl = new Acl_FB($this->application);
		$this->application->facebook = new Facebook(array(
			'appId'  => self::APP_ID,
			'secret' => self::SECRET,
		));
		$this->Acl->facebook = $this->application->facebook;
	}
	
	private function __autoload($className) {
		$classFileName = str_replace('_',DIRECTORY_SEPARATOR,$className).'.php';
		if ($className == 'Facebook') {
			require_once ROOT_BACK.'includes'.DS.'facebook'.DS.strtolower($classFileName);
		} else {
			require_once ROOT_BACK.MODELS_DIRECTORY.DS.$classFileName;
		}
	}
	
	protected static function get_method($name) {
		$class = new ReflectionClass('Acl_FB');
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
	}
	
	public function test_test_user() {
		$session = self::$VALID_EXPIRED_SESSION;
		$this->application->facebook->setSession($session);
		$this->assertTrue(
			$this->Acl->test_user(array('1677846385'))
		);
	
		$this->application->FBID = 2;
		$this->assertTrue(
			$this->Acl->test_user(array('2'))
		);
		$this->assertFalse(
			$this->Acl->test_user(array('3'))
		);
		$this->assertTrue(
			$this->Acl->test_user(array(2))
		);
	}
	
	public function test_test_friend() {
		// STUB
	}
}
?>