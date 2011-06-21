<?php
require_once('globals.php');

/**
 *	Unit Test for the Calendar_DateTime class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends ImpactPHPUnit
 */
class Test_Calendar_DateTime extends ImpactPHPUnit {
	private $testPath;
	
	protected function setUp() {
        $this->init();
	}
	
	public function test_set_year() {
		$this->instance->year = 2010;
		$this->assertEquals(2010,$this->instance->year);
	}
	
	public function test_set_month() {
		$this->instance->month = 2;
		$this->assertEquals(2,$this->instance->month);
		$this->instance->month = 'feb';
		$this->assertEquals(2,$this->instance->month);
		$this->instance->month = 'FEBRUARY';
		$this->assertEquals(2,$this->instance->month);
		$this->instance->month = -10;
		$this->assertEquals(2,$this->instance->month);
	}
	
	public function test_set_day() {
		$this->instance->day = 22;
		$this->assertEquals(22,$this->instance->day);
		
		$this->instance->month = 'feb';
		$this->instance->year = 2010;
		$this->instance->day = -6;
		$this->assertEquals(22,$this->instance->day);
		
		$this->instance->month = 'feb';
		$this->instance->year = 2012;
		$this->instance->day = -6;
		$this->assertEquals(23,$this->instance->day);
	}
	
	public function test_set_hours() {
		$this->instance->hours = 21;
		$this->assertEquals(21,$this->instance->hours);
		
		$this->instance->hours = -3;
		$this->assertEquals(21,$this->instance->hours);
	}
	
	public function test_set_minutes() {
		$this->instance->minutes = 33;
		$this->assertEquals(33,$this->instance->minutes);
		
		$this->instance->minutes = -27;
		$this->assertEquals(33,$this->instance->minutes);
	}
	
	public function test_set_seconds() {
		$this->instance->minutes = 33;
		$this->assertEquals(33,$this->instance->minutes);
		
		$this->instance->minutes = -27;
		$this->assertEquals(33,$this->instance->minutes);
	}
	
	public function test_set_year_day() {
		// STUB
	}
	
	public function test_set_week() {
		// STUB
	}
	
	public function test_set_week_day() {
		// STUB
	}
}
?>