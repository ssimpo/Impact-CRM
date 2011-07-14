<?php
require_once('globals.php');

/**
 *	Unit Test for the Filesystem_File_text class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends ImpactPHPUnit
 */
class Test_Filesystem_File_Text extends ImpactPHPUnit {
	private $testPath;
	
	protected function setUp() {
		$this->testPath = realpath(ROOT_BACK.DS.'util'.DS.'unit_tests'.DS.MODELS_DIRECTORY.DS.'Filesystem'.DS.'_data');
        $this->init();
	}
	
	public function test_load() {
		$filepath = $this->testPath.DS.'01012011.log';
		$this->assertMethodPropertyType(
			'handle','resource',
			array($filepath,'r')
		);
    }
	
	public function test_next() {
		$filepath = $this->testPath.DS.'01012011.log';
        $this->instance->load($filepath,'r');
		$this->assertEquals("1\n",$this->instance->next());
    }
	
	public function test_all() {
        $filepath = $this->testPath.DS.'01012011.log';
        $this->instance->load($filepath,'r');
		$this->assertEquals("1\n2\n3",$this->instance->all());
    }
}
?>