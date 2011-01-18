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
class TEST_I extends PHPUnit_Framework_TestCase {
    protected function setUp() {
        $debug = debug_backtrace();
	$path = dirname($debug[0]['file']);
        //$path = str_replace('\\', '/', $path);
        //$path = preg_replace('/\tests\Z/', '/', $path);
        include_once 'H:\Projects\ImpactCRM\ImpactCRM\models\class.I.php';
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
        $this->assertEquals(
            __DIR__,
            I::get_include_directory()
        );
    }
    
    
}
?>