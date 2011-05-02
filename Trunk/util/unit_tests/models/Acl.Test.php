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
class Test_Acl extends PHPUnit_Framework_TestCase {
	private $Acl;
	
	protected function setUp() {
		if (!defined('__DIR__')) {
			$iPos = strrpos(__FILE__, "/");
			define('__DIR__', substr(__FILE__, 0, $iPos) . '/');
		}
		if (!defined('DS')) {
			define('DS',DIRECTORY_SEPARATOR);
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
		spl_autoload_register('self::__autoload');
		
		$application = Application::instance();
		$this->Acl = new Acl($application);
	}
	
	private function __autoload($className) {
		$classFileName = str_replace('_',DIRECTORY_SEPARATOR,$className).'.php';
	require_once ROOT_BACK.MODELS_DIRECTORY.DS.$classFileName;
	}
	
	protected static function get_method($name) {
		$class = new ReflectionClass('Acl');
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
	}
	
	public function test_load_roles() {
		$this->Acl->load_roles('[WEB][ADMIN][DEV]');
		$method = self::get_method('allowed');
		
		$this->assertFalse(
			$method->invokeArgs($this->Acl, array('[WEB2]'))
		);
		
		$this->Acl->load_roles('[WEB2]');
		$this->assertTrue(
			$method->invokeArgs($this->Acl, array('[WEB2]'))
		);
	}
	
	public function test_allowed() {
		$this->Acl->load_roles('[WEB][ADMIN][DEV]');
		$method = self::get_method('allowed');
		
		$this->assertTrue(
			$method->invokeArgs($this->Acl, array('[WEB][FB:USER:93][DEVELOPER]','[WEB2]'))
		);
		$this->assertFalse(
			$method->invokeArgs($this->Acl, array('[WEB],[FB:USER:93],[DEVELOPER]','[DEV]'))
		);
		$this->assertTrue(
			$method->invokeArgs($this->Acl, array('[WEB]'))
		);
		$this->assertFalse(
			$method->invokeArgs($this->Acl, array('','[WEB]'))
		);
		$this->assertFalse(
			$method->invokeArgs($this->Acl, array('[DEV],[ADMIN]','[WEB]'))
		);
		$this->assertFalse(
			$method->invokeArgs($this->Acl, array('[WEB2]','[WEB][ADMIN][WEB3]'))
		);
	}
	
	public function test_test_role() {
		$this->Acl->load_roles('[WEB][ADMIN][DEV]');
		$method = self::get_method('test_role');
		
		/*$this->assertTrue(
			$method->invokeArgs($this->Acl, array('[WEB][FB:USER:93][DEVELOPER]'))
		);
		$this->assertFalse(
			$method->invokeArgs($this->Acl, array('[WEB2][FB:USER:93][DEVELOPER]'))
		);*/
	}
	
	public function test_split_special_role() {
		$method = self::get_method('_split_special_role');
		
		/*$this->assertEquals(
			array('FB','USER',array('93')),
			$method->invokeArgs($this->Acl, array('[FB:USER:93]'))
		);
		$this->assertEquals(
			array('GEO','RADIUS',array('39.0335','-78.4838','90','KM')),
			$method->invokeArgs($this->Acl, array('[GEO:RADIUS:39.0335:-78.4838:90:KM]'))
		);*/
	}
}
?>