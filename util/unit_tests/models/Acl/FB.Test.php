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
    const APP_ID = '117743971608120';
    const SECRET = '943716006e74d9b9283d4d5d8ab93204';
    
    private static $VALID_EXPIRED_SESSION = array(
	'access_token' => '117743971608120|2.vdCKd4ZIEJlHwwtrkilgKQ__.86400.1281049200-1677846385|NF_2DDNxFBznj2CuwiwabHhTAHc.',
	'expires'      => '1281049200',
	'secret'       => 'u0QiRGAwaPCyQ7JE_hiz1w__',
	'session_key'  => '2.vdCKd4ZIEJlHwwtrkilgKQ__.86400.1281049200-1677846385',
	'sig'          => '7a9b063de0bef334637832166948dcad',
	'uid'          => '1677846385',
    );
  
    private $Acl;
    
    protected function setUp() {
        if(!defined('DS')) { define('DS',DIRECTORY_SEPARATOR); }
        if(!defined('MODELS_DIRECTORY')) { define('MODELS_DIRECTORY','models'); }
        if(!defined('ROOT_BACK')) { define('ROOT_BACK',__DIR__.DS.'..'.DS.'..'.DS.'..'.DS.'..'.DS); }
        spl_autoload_register('self::__autoload');
        
        $application = Application::instance();
        //$application->facebook =  $this->getMock('Facebook');
        $this->Acl = new Acl($application);
	$application->facebook = new Facebook(array(
	    'appId'  => self::APP_ID,
	    'secret' => self::SECRET,
	));
        $this->Acl->facebook = $application->facebook;
        /*$this->Acl->facebook->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue(1));*/
	    
	$this->Acl_FB = new Acl_FB($application);
       
    }
    
    private function __autoload($className) {
        $classFileName = str_replace('_',DIRECTORY_SEPARATOR,$className).'.php';
	require_once ROOT_BACK.MODELS_DIRECTORY.DIRECTORY_SEPARATOR.$classFileName;
    }
    
    protected static function getMethod($name) {
        $class = new ReflectionClass('Acl_FB');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
    
    public function test_test() {
	$session = self::$VALID_EXPIRED_SESSION;
	$application = Application::instance();
	$application->facebook->setSession($session);
	
	$this->assertTrue(
            $this->Acl_FB->test('USER','1677846385')
        );
    }
    
    public function test_test_user() {
	$method = self::getMethod('_test_user');
	$session = self::$VALID_EXPIRED_SESSION;
	$application = Application::instance();
	$application->facebook->setSession($session);
	
	$this->assertTrue(
	    $method->invokeArgs($this->Acl_FB, array('1677846385'))
        );
	
	$application = Application::instance();
        $application->FBID = 2;
        $this->assertTrue(
            $method->invokeArgs($this->Acl_FB, array('2'))
        );
        $this->assertFalse(
            $method->invokeArgs($this->Acl_FB, array('3'))
        );
	$this->assertTrue(
            $method->invokeArgs($this->Acl_FB, array(2))
        );
    }
}

/*class Facebook {
    public function getUser() {
        
    }
}*/

?>