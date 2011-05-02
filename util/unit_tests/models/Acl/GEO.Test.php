<?php
/**
 *	  Unit Test for the Acl class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	  @package UnitTests.Impact
 *	  @extends PHPUnit_Framework_TestCase
 */
class Test_Acl_GEO extends PHPUnit_Framework_TestCase {
	private $Acl;
	
	protected function setUp() {
		if (!defined('__DIR__')) {
			$iPos = strrpos(__FILE__, "/");
			define('__DIR__', substr(__FILE__, 0, $iPos) . '/');
		}
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
		
		$this->Acl = new Acl_GEO();
	}
	
	private function __autoload($className) {
		$classFileName = str_replace('_',DS,$className).'.php';
		require_once ROOT_BACK.MODELS_DIRECTORY.DS.$classFileName;
	}
	
	protected static function get_method($name) {
		$class = new ReflectionClass('Acl');
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
	}
	
	public function test_test_city() {
		$_SERVER['REMOTE_ADDR'] = '166.56.23.1';
		$this->assertTrue(
			$this->Acl->test_city(array('ASHBURN'))
		);
	}
	
	public function test_test_country() {
		$_SERVER['REMOTE_ADDR'] = '166.56.23.1';
		$this->assertTrue(
			$this->Acl->test_country(array('US'))
		);
	}
	
	public function test_test_region() {
		$_SERVER['REMOTE_ADDR'] = '166.56.23.1';
		$this->assertTrue(
			$this->Acl->test_region(array('VA'))
		);
	}
	
	public function test_test_radius() {
		$_SERVER['REMOTE_ADDR'] = '166.56.23.1';
		$this->assertTrue(
			$this->Acl->test_radius(array('39.0335','-78.4838','90','KM'))
		);
		$this->assertTrue(
			$this->Acl->test_radius(array('39.0335','-78.4838','56','M'))
		);
	}
}
?>