<?php
require_once('globals.php');

/**
 *	Unit Test for the Filesystem_File_Csv class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends ImpactPHPUnit
 */
class Test_Filesystem_File_Csv extends ImpactPHPUnit {
	private $testPath;
	
	protected function setUp() {
		$this->testPath = realpath(ROOT_BACK.DS.'util'.DS.'unit_tests'.DS.MODELS_DIRECTORY.DS.'Filesystem'.DS.'_data');
        $this->init();
	}
	
	public function test_get_headers() {
		// STUB
    }
	
	public function test_set_data_type() {
		// STUB
    }
	
	public function test_set_header() {
		// STUB
    }
	
	public function test_get_header_index() {
		// STUB
    }
	
	public function test_init_headers() {
		// STUB
    }
	
	public function test_parse() {
		// STUB
    }
	
	public function test_use_column_headers() {
		// STUB
    }
	
	public function test_get_correct_type() {
		// STUB
    }
	
	public function test_get_column_type() {
		// STUB
    }
	
	public function test_get_int_value() {
		$this->assertMethodReturn(1,'1');
		$this->assertMethodReturn(0,'');
		$this->assertMethodReturn(0,'blah');
    }
	
	public function test_get_boolean_value() {
		$this->assertMethodReturnTrue('on');
		$this->assertMethodReturnTrue('yes');
		$this->assertMethodReturnTrue('true');
		$this->assertMethodReturnFalse('false');
		$this->assertMethodReturnFalse('off');
		$this->assertMethodReturnFalse('no');
    }
	
	public function test_get_date_value() {
		// STUB
    }
	
	public function test_parse_columns() {
		$this->assertMethodReturn(
			array('test "1"','test','test'),
			array(array('test ""1""','test','test'."\n"))
		);
		$this->assertMethodReturn(
			array('col1'=>'test "1"','col2'=>'test','col3'=>'test'),
			array('col1'=>'test ""1""','col2'=>'test','col3'=>'test'."\n")
		);
    }
	
	public function test_write() {
		// STUB
    }

	public function test_rebuild_line() {
		// STUB
    }
	
	public function test_is_numeric_indexed_array() {
		$this->assertMethodReturnTrue(array(array('test','test','test')));
		$this->assertMethodReturnFalse(array('col1'=>'test','test','test'));
    }
}
?>