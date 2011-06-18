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
        $method = self::get_method('_media_detect');
        
        $method->invoke($this->instance);
        $this->assertEquals('[PC]',$this->instance->media);
        
		if (!defined('DOMAIN')) {
			define('DOMAIN','m.test.com');
		}
        $method->invoke($this->instance);
        $this->assertEquals('[MOBILE]',$this->instance->media);
        
        $_GET['media'] = 'FB';
        $method->invoke($this->instance);
        $this->assertEquals('[FB]',$this->instance->media);
    }
    
    public function test_language_detect() {
		if (!defined('DEFAULT_LANG')) {
			define('DEFAULT_LANG','en_GB');
		}
        $method = self::get_method('_language_detect');
        
        $method->invoke($this->instance);
        $this->assertEquals('[EN_GB]',$this->instance->language);
        
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US';
        $method->invoke($this->instance);
        $this->assertEquals('[EN_US]',$this->instance->language);
        
        $_GET['lang'] = 'zh';
        $method->invoke($this->instance);
        $this->assertEquals('[ZH]',$this->instance->language);
    }
}