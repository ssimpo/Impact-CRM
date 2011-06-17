<?php
/**
 *	Unit Test for the Application class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends PHPUnit_Framework_TestCase
 */
class Test_Application extends PHPUnit_Framework_TestCase {
    private $application;
    
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
			define('ROOT_BACK',__DIR__.DS.'..'.DS.'..'.DS.'..'.DS);
		}
		if (!defined('DIRECT_ACCESS_CHECK')) {
            define('DIRECT_ACCESS_CHECK',false);
        }
		spl_autoload_register('self::__autoload');
        
        $this->application = Application::instance();
		$this->Acl = new Acl($this->application);
    }
    
    private function __autoload($className) {
		$classFileName = str_replace('_',DS,$className).'.php';
		require_once ROOT_BACK.MODELS_DIRECTORY.DS.$classFileName;
    }
    
    protected static function get_method($name) {
		$class = new ReflectionClass('Application');
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
    }
    
    public function test_property_exists() {
        $this->application->blah = 25;
        $this->assertTrue($this->application->property_exists('blah'));
        $this->assertFalse($this->application->property_exists('blahblah'));
    }
    
    public function test_set_get() {
        $this->application->blah = 25;
        $this->assertEquals($this->application->blah,25);
        
        $settings = $this->application->settings;
        $this->assertEquals($settings,array('blah'=>25));
    }
    
    public function test_get_page_request_info() {
        // STUB
    }
    
    public function test_user_access_detect() {
        // STUB
    }
    
    public function test_media_detect() {
		if (!defined('DEFAULT_MEDIA')) {
			define('DEFAULT_MEDIA','PC');
		}
        $method = self::get_method('_media_detect');
        
        $method->invoke($this->application);
        $this->assertEquals('[PC]',$this->application->media);
        
		if (!defined('DOMAIN')) {
			define('DOMAIN','m.test.com');
		}
        $method->invoke($this->application);
        $this->assertEquals('[MOBILE]',$this->application->media);
        
        $_GET['media'] = 'FB';
        $method->invoke($this->application);
        $this->assertEquals('[FB]',$this->application->media);
    }
    
    public function test_language_detect() {
		if (!defined('DEFAULT_LANG')) {
			define('DEFAULT_LANG','en_GB');
		}
        $method = self::get_method('_language_detect');
        
        $method->invoke($this->application);
        $this->assertEquals('[EN_GB]',$this->application->language);
        
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US';
        $method->invoke($this->application);
        $this->assertEquals('[EN_US]',$this->application->language);
        
        $_GET['lang'] = 'zh';
        $method->invoke($this->application);
        $this->assertEquals('[ZH]',$this->application->language);
    }
}