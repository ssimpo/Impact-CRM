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
        spl_autoload_register('self::__autoload');
        $this->Acl = new Acl;
    }
    
    private function __autoload($className) {
        if (stristr($className,'_base') !== false) {
            $className = str_replace('_Base','',$className);
            include_once '../models/base.'.str_replace('_','',$className).'.php';
        } else {
            include_once '../models/class.'.str_replace('_','',$className).'.php';
        }
    }
    
    protected static function getMethod($name) {
        $class = new ReflectionClass('Acl');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
    
    public function test_load_roles() {
    }
    
    public function test_allowed() {
        $this->Acl->load_roles('[WEB][ADMIN][DEV]');
        $method = self::getMethod('allowed');
        
        $this->assertTrue(
            $method->invokeArgs($this->Acl, array('[WEB][FBUSER:93][DEVELOPER]','[WEB2]'))
        );
        $this->assertFalse(
            $method->invokeArgs($this->Acl, array('[WEB],[FBUSER:93],[DEVELOPER]','[DEV]'))
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
        /*$this->Acl->load_roles('[WEB][ADMIN][DEV]');
        $method = self::getMethod('test_role');
        
        $this->assertTrue(
            $method->invokeArgs($this->Acl, array('[WEB][FBUSER:93][DEVELOPER]'))
        );
        $this->assertFalse(
            $method->invokeArgs($this->Acl, array('[WEB2][FBUSER:93][DEVELOPER]'))
        );*/
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
        
        $this->assertEquals(
            'FB',
            $method->invokeArgs($this->Acl, array('[FB:USER:93]'))
        );
    }
}
?>