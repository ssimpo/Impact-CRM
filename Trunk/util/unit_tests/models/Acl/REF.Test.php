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
class Test_Acl_REF extends PHPUnit_Framework_TestCase {
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
		if (!defined('DIRECT_ACCESS_CHECK')) {
            define('DIRECT_ACCESS_CHECK',false);
        }
		spl_autoload_register('self::__autoload');
		
		$this->Acl = new Acl_REF();
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
	
	public function test_test_keywords() {
		$_SERVER['HTTP_REFERER'] = 'http://www.google.co.uk/search?hl=en&xhr=t&q=churches+in+middlesbrough&cp=16&pf=p&sclient=psy&safe=off&aq=0&aqi=&aql=&oq=churches+in+midd&pbx=1&fp=2d2ccc87393ce188';
		$this->assertTrue(
			$this->Acl->test_keywords(array('MIDDLESBROUGH','CHURCHES'))
		);
		$this->assertFalse(
			$this->Acl->test_keywords(array('LONDON','CHURCHES'))
		);
	}
}
?>