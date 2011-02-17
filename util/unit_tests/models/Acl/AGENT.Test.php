<?php
/**
 *      Unit Test for the Acl class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *      @package UnitTests.Impact
 *      @extends PHPUnit_Framework_TestCase
 */
class Test_Acl_AGENT extends PHPUnit_Framework_TestCase {
    private $Acl;
    
    protected function setUp() {
        if(!defined('DS')) { define('DS',DIRECTORY_SEPARATOR); }
        if(!defined('MODELS_DIRECTORY')) { define('MODELS_DIRECTORY','models'); }
        if(!defined('ROOT_BACK')) { define('ROOT_BACK',__DIR__.DS.'..'.DS.'..'.DS.'..'.DS.'..'.DS); }
        spl_autoload_register('self::__autoload');
        
        $application = Application::instance();
        $this->Acl = new Acl($application);
    }
    
    private function __autoload($className) {
        $classFileName = str_replace('_',DIRECTORY_SEPARATOR,$className).'.php';
	require_once ROOT_BACK.MODELS_DIRECTORY.DIRECTORY_SEPARATOR.$classFileName;
    }
    
    protected static function getMethod($name) {
        $class = new ReflectionClass('Acl');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
    
    public function test_test_role() {
	$method = self::getMethod('test_role');
        
	$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10.5; en-US; rv:1.9.0.3) Gecko/2008092414 Firefox/3.0.3';
        $this->assertTrue(
            $method->invokeArgs($this->Acl, array('[AGENT:BROWSER:FIREFOX]'))
        );
    }
    
    public function test_test_special_role() {
        $method = self::getMethod('test_special_role');
        
	$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10.5; en-US; rv:1.9.0.3) Gecko/2008092414 Firefox/3.0.3';
        $this->assertTrue(
            $method->invokeArgs($this->Acl, array('[AGENT:BROWSER:FIREFOX]'))
        );
        $this->assertTrue(
            $method->invokeArgs($this->Acl, array('[AGENT:PLATFORM:MACOSX]'))
        );
        $this->assertFalse(
            $method->invokeArgs($this->Acl, array('[AGENT:MOBILE]'))
        );
        
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Linux; U; Android 0.5; en-us) AppleWebKit/522+ (KHTML, like Gecko) Safari/419.3';
        $this->assertTrue(
            $method->invokeArgs($this->Acl, array('[AGENT:MOBILE]'))
        );
    }
}
?>