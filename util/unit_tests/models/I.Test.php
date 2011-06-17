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
class Test_I extends PHPUnit_Framework_TestCase {
	
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
		if (!defined('USE_LOCAL_MODELS')) {
            define('USE_LOCAL_MODELS',false);
        }
		spl_autoload_register('self::__autoload');
	}
	
	private function __autoload($className) {
		$classFileName = str_replace('_',DS,$className).'.php';
		require_once ROOT_BACK.MODELS_DIRECTORY.DS.$classFileName;
	}
	
	public function test_reformat_role_string() {
		$this->assertEquals(
			'[WEB],[ADMIN],[DEV]',
			I::reformat_role_string('WEB,ADMIN,DEV')
		);
		$this->assertEquals(
			'[WEB],[ADMIN],[DEV]',
			I::reformat_role_string(' WEB, ADMIN , DEV ')
		);
		$this->assertEquals(
			'[WEB],[ADMIN],[DEV]',
			I::reformat_role_string('[WEB], [ ADMIN], [DEV]')
		);
		$this->assertEquals(
			'[WEB],[ADMIN 2],[DEV 5]',
			I::reformat_role_string('[WEB ], [ ADMIN 2], DEV 5')
		);
		$this->assertEquals(
			'[WEB],[ADMIN 2],[DEV 5]',
			I::reformat_role_string(' [WEB ][ ADMIN 2], DEV 5 ')
		);
		$this->assertEquals(
			'[WEB],[ADMIN 2],[DEV 5]',
			I::reformat_role_string(array(' [WEB ][ ADMIN 2], DEV 5 '))
		);
	}
	
	public function test_get_include_directory() {
		$this->assertEquals(__DIR__,I::get_include_directory());
	}
	
	public function test_camelize() {
		$this->assertEquals('setDate',I::camelize('set_date'));
		$this->assertEquals('setThisDate',I::camelize('set_this_date'));
		$this->assertEquals('set',I::camelize('Set'));
		$this->assertEquals('_setDate',I::camelize('_set_date'));
		$this->assertEquals('__setDate',I::camelize('__set_date'));
		$this->assertEquals('__setDate_',I::camelize('__set_date_'));
		$this->assertEquals('fbid',I::camelize('FBID'));
	}
	
	public function test_is_camelcase() {
		$this->assertTrue(I::is_camelcase('setDate'));
		$this->assertFalse(I::is_camelcase('SETDATE'));
		$this->assertFalse(I::is_camelcase('setdate'));
	}
	
	public function test_variable_to_function() {
		$this->assertEquals('set_date',I::uncamelize('setDate'));
		$this->assertEquals('set_this_date',I::uncamelize('setThisDate'));
		$this->assertEquals('set',I::uncamelize('Set'));
		$this->assertEquals('set_date',I::uncamelize('SetDate'));
		$this->assertEquals('_set_date',I::uncamelize('_setDate'));
		$this->assertEquals('__set_date',I::uncamelize('__setDate'));
		$this->assertEquals('__set_date_',I::uncamelize('__setDate_'));
	}
	
	public function test_contains() {
		$this->assertTrue(I::contains('Impact Project','act'));
		$this->assertTrue(I::contains('IMPACT Project','act'));
		$this->assertFalse(I::contains('IMPACT Project','actt'));
	}
	
	public function test_array_trim() {
		$this->assertEquals(
			array(),
			I::array_trim(array('','',''))
		);
		$this->assertEquals(
			array('1','2'),
			I::array_trim(array('1','','2'))
		);
		$this->assertEquals(
			array('1','2','3'),
			I::array_trim(array('1 ','2 ',' 3','  '))
		);
		
		$this->assertEquals(
			array('one'=>'1','two'=>'2','three'=>'3'),
			I::array_trim(array('one'=>'1','two'=>'2','three'=>'3','four'=>''))
		);
		$this->assertEquals(
			array('one'=>'1','two'=>'2','three'=>'3'),
			I::array_trim(array('one'=>'1 ','two'=>' 2','three'=>' 3 '))
		);
	}
}
?>