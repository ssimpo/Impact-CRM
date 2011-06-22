<?php
require_once('globals.php');

/**
 *	Unit Test for the Application class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends ImpactPHPUnit
 */
class Test_Application extends ImpactPHPUnit {
    
    protected function setUp() {
		$this->init();
		$this->Acl = new Acl($this->instance);
    }
    
    public function test_property_exists() {
        $this->instance->blah = 25;
        $this->assertTrue($this->instance->property_exists('blah'));
        $this->assertFalse($this->instance->property_exists('blahblah'));
    }
    
    public function test_set_get() {
        $this->instance->blah = 25;
        $this->assertEquals($this->instance->blah,25);
        
        $settings = $this->instance->settings;
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
		$this->assertMethodPropertySet('media','[PC]');
        
        
		if (!defined('DOMAIN')) {
			define('DOMAIN','m.test.com');
		}
		$this->assertMethodPropertySet('media','[MOBILE]');
        
        $_GET['media'] = 'FB';
        $this->assertMethodPropertySet('media','[FB]');
    }
    
    public function test_language_detect() {
		if (!defined('DEFAULT_LANG')) {
			define('DEFAULT_LANG','en_GB');
		}
		$this->assertMethodPropertySet('language','[EN_GB]',$this->instance);
        
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US';
		$this->assertMethodPropertySet('language','[EN_US]',$this->instance);
        
        $_GET['lang'] = 'zh';
		$this->assertMethodPropertySet('language','[ZH]',$this->instance);
    }
}