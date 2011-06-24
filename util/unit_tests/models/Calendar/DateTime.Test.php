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
	
	public function test_set_date() {
		$this->assertMethodReturn(
			mktime(11,0,0,6,24,2011),array(2011,6,24,11,0,0)
		);
		$this->assertMethodReturn(
			mktime(11,0,0,6,24,2010),array(2010,6,24,11,0,0)
		);
		$this->assertMethodReturn(
			mktime(11,0,0,2,29,2012),array(12,'Feb',29,11,0,0)
		);
	}
	
	public function test_adjust() {
		$this->instance->set_date(2011,6,24,11,0,0);
		$this->assertMethodReturn(mktime(11,0,0,6,24,2012),1);
		$this->assertMethodReturn(
			mktime(11,0,0,4,24,2012),array(0,-2)
		);
		$this->assertMethodReturn(
			mktime(11,0,0,4,21,2012),array(0,0,-3)
		);
		$this->assertMethodReturn(
			mktime(16,0,0,4,21,2012),array(0,0,0,5)
		);
		$this->assertMethodReturn(
			mktime(15,57,0,4,21,2012),array(0,0,0,0,-3)
		);
		$this->assertMethodReturn(
			mktime(15,57,20,4,21,2012),array(0,0,0,0,0,20)
		);
		
		$this->instance->set_date(2011,6,24,11,0,0);
		$this->assertMethodReturn(
			mktime(11,0,0,5,31,2011),array(0,0,-24)
		);
	}
	
	public function test_adjust_year() {
		$this->instance->set_date(2011,6,24,11,0,0);
		$this->assertMethodReturn(mktime(11,0,0,6,24,2012),1);
		$this->assertMethodReturn(mktime(11,0,0,6,24,2010),-2);
	}
	
	public function test_adjust_month() {
		$this->instance->set_date(2011,6,24,11,0,0);
		$this->assertMethodReturn(mktime(11,0,0,7,24,2011),1);
		$this->assertMethodReturn(mktime(11,0,0,5,24,2011),-2);
	}
	
	public function test_adjust_week() {
		$this->instance->set_date(2011,6,24,11,0,0);
		$this->assertMethodReturn(mktime(11,0,0,7,1,2011),1);
		$this->assertMethodReturn(mktime(11,0,0,6,17,2011),-2);
	}
	
	public function test_adjust_day() {
		$this->instance->set_date(2011,6,24,11,0,0);
		$this->assertMethodReturn(mktime(11,0,0,6,25,2011),1);
		$this->assertMethodReturn(mktime(11,0,0,6,23,2011),-2);
	}
	
	public function test_adjust_hours() {
		$this->instance->set_date(2011,6,24,11,0,0);
		$this->assertMethodReturn(mktime(12,0,0,6,24,2011),1);
		$this->assertMethodReturn(mktime(10,0,0,6,24,2011),-2);
	}
	
	public function test_adjust_minutes() {
		$this->instance->set_date(2011,6,24,11,0,0);
		$this->assertMethodReturn(mktime(11,1,0,6,24,2011),1);
		$this->assertMethodReturn(mktime(10,59,0,6,24,2011),-2);
	}
	
	public function test_adjust_seconds() {
		$this->instance->set_date(2011,6,24,11,0,0);
		$this->assertMethodReturn(mktime(11,0,1,6,24,2011),1);
		$this->assertMethodReturn(mktime(10,59,59,6,24,2011),-2);
	}
	
	public function test_set_part() {
		$this->instance->set_date(2011,6,24,11,0,0);
		
		$this->assertMethodReturn(
			mktime(11,0,0,6,24,2010),array(2010,'year')
		);
		$this->assertMethodReturn(
			mktime(11,0,0,2,24,2010),array(2,'mon')
		);
		$this->assertMethodReturn(
			mktime(11,0,0,2,23,2010),array(23,'mday')
		);
		$this->assertMethodReturn(
			mktime(12,0,0,2,23,2010),array(12,'hours')
		);
		$this->assertMethodReturn(
			mktime(12,23,0,2,23,2010),array(23,'minutes')
		);
		$this->assertMethodReturn(
			mktime(12,23,23,2,23,2010),array(23,'seconds')
		);
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
		$this->instance->set_date(2011,6,24,11,0,0);
		$this->assertMethodReturn(mktime(11,0,0,6,1,2011),152);
		
		$this->instance->set_date(2011,6,24,11,0,0);
		$this->assertMethodReturn(mktime(11,0,0,6,1,2011),-213);
	}
	
	public function test_get_week() {
		// STUB
	}
	
	public function test_set_week() {
		// STUB
	}
	
	public function test_get_week_day() {
		// STUB
	}
	
	public function test_set_week_day() {
		// STUB
	}
	
	public function test_get_month_number() {
		$this->assertMethodReturn(2,'FEB');
		$this->assertMethodReturn(2,'February');
		$this->assertMethodReturn(2,2);
		$this->assertMethodReturn(7,'july');
	}
	
	public function test_get_month_length() {
		$this->assertMethodReturn(30,array(2012,'april'));
		$this->assertMethodReturn(30,array(2012,'apr'));
		$this->assertMethodReturn(30,array(2012,4));
		$this->assertMethodReturn(29,array(2012,2));
		$this->assertMethodReturn(28,array(2011,'FEBRUARY'));
	}
	
	public function test_is_leap_year() {
		$this->assertMethodReturnTrue(2012);
		$this->assertMethodReturnFalse(2011);
		$this->assertMethodReturnTrue(2000);
		$this->assertMethodReturnFalse(1900);
	}
	
	public function test_get_four_digit_year() {
		$this->assertMethodReturn(2000,0);
		$this->assertMethodReturn(2012,12);
		$this->assertMethodReturn(1954,54);
		$this->assertMethodReturn(1978,78);
		$this->assertMethodReturn(2003,2003);
	}
}
?>