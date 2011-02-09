<?php
/**
 *      Unit Test for the I class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *      @package UnitTests.Impact
 *      @extends PHPUnit_Framework_TestCase
 */
class Test_I extends PHPUnit_Framework_TestCase {
    protected function setUp() {
       spl_autoload_register('self::__autoload');
    }
    
    private function __autoload($className) {
        if (stristr($className,'_base') !== false) {
            $className = str_replace('_Base','',$className);
            include_once '../models/base.'.str_replace('_','',$className).'.php';
        } else {
            include_once '../models/class.'.str_replace('_','',$className).'.php';
        }
    }
    
    public function test_add_square_brakets() {
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
    
    public function test_function_to_variable() {
	$this->assertEquals('setDate',I::function_to_variable('set_date'));
	$this->assertEquals('setThisDate',I::function_to_variable('set_this_date'));
	$this->assertEquals('set',I::function_to_variable('Set'));
	$this->assertEquals('_setDate',I::function_to_variable('_set_date'));
	$this->assertEquals('__setDate',I::function_to_variable('__set_date'));
	$this->assertEquals('__setDate_',I::function_to_variable('__set_date_'));
    }
    
    public function test_variable_to_function() {
	$this->assertEquals('set_date',I::variable_to_function('setDate'));
	$this->assertEquals('set_this_date',I::variable_to_function('setThisDate'));
	$this->assertEquals('set',I::variable_to_function('Set'));
	$this->assertEquals('set_date',I::variable_to_function('SetDate'));
	$this->assertEquals('_set_date',I::variable_to_function('_setDate'));
	$this->assertEquals('__set_date',I::variable_to_function('__setDate'));
	$this->assertEquals('__set_date_',I::variable_to_function('__setDate_'));
    }
    
    public function test_contains() {
	$this->assertTrue(I::contains('Impact Project','act'));
	$this->assertTrue(I::contains('IMPACT Project','act'));
	$this->assertFalse(I::contains('IMPACT Project','actt'));
    }
}
?>