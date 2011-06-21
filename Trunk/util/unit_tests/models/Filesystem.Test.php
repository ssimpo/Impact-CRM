<?php
require_once('globals.php');

/**
 *	Unit Test for the Filesystem class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends ImpactPHPUnit
 */
class Test_Filesystem extends ImpactPHPUnit {
	
	protected function setUp() {
        $this->init('Filesystem_Directory');
	}
	
	public function test_is_resource() {
		$this->assertMethodReturnFalse();
        $this->instance->set_directory();
		$this->assertMethodReturnTrue();
		$this->instance->close();
		$this->assertMethodReturnFalse();
    }
	
	public function test_is_equal() {
        $this->assertMethodReturnTrue(array('TEST','  test'));
		$this->assertMethodReturnTrue(array('  TEST','test'));
		$this->assertMethodReturnTrue(array('TESt',' test'));
		$this->assertMethodReturnTrue(array(1,' 1'));
		$this->assertMethodReturnTrue(array(1.3,' 1.3'));
    }
}
?>