<?php
/**
 *	  Unit Test for the I class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends PHPUnit_Framework_TestCase
 */
class Test_Application extends PHPUnit_Framework_TestCase {
	
    protected function setUp() {
        if (!defined('DS')) {
	    define('DS',DIRECTORY_SEPARATOR);
	}
	if (!defined('MODELS_DIRECTORY')) {
	    define('MODELS_DIRECTORY','models');
	}
            if (!defined('ROOT_BACK')) {
		define('ROOT_BACK',__DIR__.DS.'..'.DS.'..'.DS.'..'.DS);
	}
	spl_autoload_register('self::__autoload');
    }
    
    private function __autoload($className) {
	$classFileName = str_replace('_',DIRECTORY_SEPARATOR,$className).'.php';
	require_once ROOT_BACK.MODELS_DIRECTORY.DIRECTORY_SEPARATOR.$classFileName;
    }
    
    public function test_property_exists(){
        // STUB
    }
    
    public function test_get_page_request_info(){
        // STUB
    }
    
    public function test_user_access_detect(){
        // STUB
    }
    
    public function test_media_detect(){
        // STUB
    }
    
    public function test_language_detect(){
        // STUB
    }
}