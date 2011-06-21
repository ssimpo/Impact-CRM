<?php
require_once('globals.php');

/**
 *	Unit Test for the Filesystem_Directory class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends ImpactPHPUnit
 */
class Test_Filesystem_Directory extends ImpactPHPUnit {
	private $testPath;
	
	protected function setUp() {
		$this->testPath = realpath(ROOT_BACK.'util'.DS.'unit_tests'.DS.MODELS_DIRECTORY.DS.'Filesystem'.DS.'_data');
        $this->init();
	}
	
	public function test_open() {
		$this->assertMethodPropertyType('handle','resource');
    }
	
	public function test_set_directory() {
		$this->assertMethodPropertySet(
			'path',$this->testPath,
			$this->testPath
		);
		$this->assertMethodPropertySet(
			'filter','/(?:.*\.log)/i',
			array($this->testPath,'*.log')
		);
    }
	
	public function test_next() {
        $this->instance->set_directory($this->testPath,'*.log');
		$this->assertMethodReturn('01012011.log');
		$this->assertMethodReturn('01022011.log');
    }
	
	public function test_reset() {
		$this->instance->set_directory($this->testPath,'*.log');
		$this->instance->next();
		$this->instance->reset();
		$this->assertEquals($this->instance->next(),'01012011.log');
    }
	
	public function test_parse_filter() {
        $this->assertMethodReturn('','');
		$this->assertMethodReturn('/.*\.log/','/.*\.log/');
		$this->assertMethodReturn('/(?:.*\.log)/i','*.log');
		$this->assertMethodReturn('/(?:.*\.log|.*\.txt)/i','*.log,*.txt');
    }
	
	public function test_set_filter() {
        $this->assertMethodPropertySet('filter','','');
		$this->assertMethodPropertySet('filter','/(?:.*\.log)/i','*.log');
    }
	
	public function test_filter() {
        $this->assertMethodReturnTrue(array('01012011.log','/(?:.*\.log)/i'));
		$this->assertMethodReturnFalse(array('01012011.log','/(?:.*\.txt)/i'));
    }
}
?>