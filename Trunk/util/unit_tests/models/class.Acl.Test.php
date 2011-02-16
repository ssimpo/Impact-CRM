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
class Test_Acl extends PHPUnit_Framework_TestCase {
    private $Acl;
    
    protected function setUp() {
        if(!defined('DS')) { define('DS',DIRECTORY_SEPARATOR); }
        if(!defined('MODELS_DIRECTORY')) { define('MODELS_DIRECTORY','models'); }
        if(!defined('ROOT_BACK')) { define('ROOT_BACK',__DIR__.DS.'..'.DS.'..'.DS.'..'.DS); }
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
    
    public function test_load_roles() {
        $this->Acl->load_roles('[WEB][ADMIN][DEV]');
        $method = self::getMethod('allowed');
        
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
        $method = self::getMethod('allowed');
        
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
        $method = self::getMethod('test_role');
        
        $this->assertTrue(
            $method->invokeArgs($this->Acl, array('[WEB][FB:USER:93][DEVELOPER]'))
        );
        $this->assertFalse(
            $method->invokeArgs($this->Acl, array('[WEB2][FB:USER:93][DEVELOPER]'))
        );
        $this->assertTrue(
            $method->invokeArgs($this->Acl, array('[WEB2][FB:USER:93][DEVELOPER][FB:USER:1]'))
        );
    }
    
    public function test_split_special_role() {
        $method = self::getMethod('_split_special_role');
        
        $this->assertEquals(
            array('FB','USER',array('93')),
            $method->invokeArgs($this->Acl, array('[FB:USER:93]'))
        );
        $this->assertEquals(
            array('GEO','TOWN',array('MIDDLESBROUGH','R','30','KM')),
            $method->invokeArgs($this->Acl, array('[GEO:TOWN:MIDDLESBROUGH:R:30:KM]'))
        );
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
        
        $application->ip = '166.56.23.1';
        $this->assertTrue(
            $method->invokeArgs($this->Acl, array('[GEO:CITY:ASHBURN]'))
        );
        
        $this->assertTrue(
            $method->invokeArgs($this->Acl, array('[GEO:RADIUS:39.0335:-78.4838:90:KM]'))
        );
        
    }
}

class Facebook {
    public function getUser() {
        
    }
}
?>