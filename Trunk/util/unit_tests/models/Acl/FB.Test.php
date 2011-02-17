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
class Test_Acl_FB extends PHPUnit_Framework_TestCase {
    private $Acl;
    
    protected function setUp() {
        if(!defined('DS')) { define('DS',DIRECTORY_SEPARATOR); }
        if(!defined('MODELS_DIRECTORY')) { define('MODELS_DIRECTORY','models'); }
        if(!defined('ROOT_BACK')) { define('ROOT_BACK',__DIR__.DS.'..'.DS.'..'.DS.'..'.DS.'..'.DS); }
        spl_autoload_register('self::__autoload');
        
        $application = Application::instance();
        $application->facebook =  $this->getMock('Facebook');
        $this->Acl = new Acl($application);
        $this->Acl->facebook = $application->facebook;
        $this->Acl->facebook->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue(1));
       
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
    
    public function test_test_special_role() {
        $method = self::getMethod('test_special_role');
        
        $this->assertTrue(
            $method->invokeArgs($this->Acl, array('[FB:USER:1]'))
        );
        
        $application = Application::instance();
        $application->FBID = 2;
        $this->assertTrue(
            $method->invokeArgs($this->Acl, array('[FB:USER:2]'))
        );
        $this->assertFalse(
            $method->invokeArgs($this->Acl, array('[FB:USER:3]'))
        );
        
    }
}

class Facebook {
    public function getUser() {
        
    }
}

?>