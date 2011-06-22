<?php
require_once('globals.php');

/**
 *	Unit Test for the Filesystem_DominoLog class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends ImpactPHPUnit
 */
class Test_Filesystem_File_DominoLog extends ImpactPHPUnit {
	private $testPath;
	
	protected function setUp() {
		$this->testPath = realpath(ROOT_BACK.DS.'util'.DS.'unit_tests'.DS.MODELS_DIRECTORY.DS.'Filesystem'.DS.'_data');
        $this->init();
	}
	
	public function test_load() {
		// STUB
    }
	
	public function test_next() {
		// STUB
    }
	
	public function test_load_config() {
        // STUB
    }
	
	public function test_parse() {
        // STUB
    }
}
?>