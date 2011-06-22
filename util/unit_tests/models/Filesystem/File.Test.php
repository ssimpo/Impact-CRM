<?php
require_once('globals.php');

/**
 *	Unit Test for the Filesystem_File class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends ImpactPHPUnit
 */
class Test_Filesystem_File extends ImpactPHPUnit {
	private $testPath;
	
	protected function setUp() {
		$this->testPath = realpath(ROOT_BACK.'util'.DS.'unit_tests'.DS.MODELS_DIRECTORY.DS.'Filesystem'.DS.'_data');
        $this->init();
	}
	
	public function test_open() {
		$this->instance->set_file($this->testPath,'01012011.log');
        $this->assertMethodPropertyType(
			'parser','Filesystem_File_Text',
			array('read')
		);
    }
	
	public function test_set_file() {
        $this->assertMethodPropertySet(
			'filename','01012011.log',array($this->testPath,'01012011.log')
		);
		$this->assertMethodPropertySet(
			'path',$this->testPath,array($this->testPath,'01012011.log')
		);
    }
	
	public function test_next() {
        /*$this->instance->set_file($this->testPath,'01012011.log');
		$this->instance->open();
		$this->assertEquals("1\n",$this->instance->next());*/
    }
	
	public function test_all() {
        /*$this->instance->set_file($this->testPath,'01012011.log');
		$this->instance->open();
		$this->assertEquals("1\n2\n3",$this->instance->all());*/
    }
	
	public function test_translate_method() {
        $this->assertMethodReturn('wt','write');
		$this->assertMethodReturn('r','read');
		$this->assertMethodReturn('a','append');
		$this->assertMethodReturn('rwt','readwrite');
    }
	
	public function test_get_filename() {
		$this->assertMethodReturn(
			'01012011.log',$this->testPath.DS.'01012011.log'
		);
    }
	
	public function test_get_ext() {
        $this->assertMethodReturn(
			'log',$this->testPath.DS.'01012011.log'
		);
    }
}
?>