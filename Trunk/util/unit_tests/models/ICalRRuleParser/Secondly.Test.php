<?php
require_once('globals.php');

/**
 *	Unit Test for the ICalRRuleParser_Secondly class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends ImpactPHPUnit
 */
class Test_ICalRRuleParser_Secondly extends ImpactPHPUnit {
	
    protected function setUp() {
        $this->init();
    }
	
	public function test_parse() {
		//STUB
	}
	
	public function test_next_interval() {
		$date1 = mktime(13,0,0,6,18,2011);
		$date2 = mktime(13,0,0,6,21,2011);
		$rrule = array('FREQ'=>'DAILY','INTERVAL'=>3);
		$this->assertMethodReturn($date2,array($date1,$rrule));
		
		$date1 = mktime(13,0,0,6,18,2011);
		$date2 = mktime(13,0,0,9,18,2011);
		$rrule = array('FREQ'=>'MONTHLY','INTERVAL'=>3);
		$this->assertMethodReturn($date2,array($date1,$rrule));
		
		$date1 = mktime(13,0,0,6,18,2011);
		$date2 = mktime(13,0,0,6,18,2014);
		$rrule = array('FREQ'=>'YEARLY','INTERVAL'=>3);
		$this->assertMethodReturn($date2,array($date1,$rrule));
	}
	
	public function test_get_seconds_in_month_period() {
		$date1 = mktime(13,0,0,6,18,2011);
		$date2 = mktime(13,0,0,7,18,2011);
		$this->assertMethodReturn($date2-$date1,$date1);
		
		$date1 = mktime(13,0,0,12,18,2011);
		$date2 = mktime(13,0,0,1,18,2012);
		$this->assertMethodReturn($date2-$date1,$date1);
		
		//Checking in a leap year
		$date1 = mktime(13,0,0,2,20,2000);
		$date2 = mktime(13,0,0,3,20,2000);
		$this->assertMethodReturn($date2-$date1,$date1);
	}
	
	public function test_get_seconds_in_year_period() {
		$date1 = mktime(13,0,0,6,18,2010);
		$date2 = mktime(13,0,0,6,18,2011);
		$this->assertMethodReturn($date2-$date1,$date1);
		
		//Leap day needed in next year
		$date1 = mktime(13,0,0,6,18,2011);
		$date2 = mktime(13,0,0,6,18,2012);
		$this->assertMethodReturn($date2-$date1,$date1);
		
		//Leap day not needed
		$date1 = mktime(13,0,0,2,18,2011);
		$date2 = mktime(13,0,0,2,18,2012);
		$this->assertMethodReturn($date2-$date1,$date1);
		
		//Leap day needed in this year
		$date1 = mktime(13,0,0,1,18,2012);
		$date2 = mktime(13,0,0,1,18,2013);
		$this->assertMethodReturn($date2-$date1,$date1);
		
		//Leap day needed in this year
		$date1 = mktime(13,0,0,2,29,2012);
		$date2 = mktime(13,0,0,3,1,2013);
		$this->assertMethodReturn($date2-$date1,$date1);
	}
	
	public function test_get_seconds_in_period() {
		$date = mktime(13,0,0,6,18,2010);
		$this->assertMethodReturn(1,array('SECONDLY',$date));
		$this->assertMethodReturn(60,array('MINUTELY',$date));
		$this->assertMethodReturn(60*60,array('HOURLY',$date));
		$this->assertMethodReturn(60*60*24,array('DAILY',$date));
		$this->assertMethodReturn(60*60*24*7,array('WEEKLY',$date));
		$this->assertMethodReturn(60*60*24*30,array('MONTHLY',$date));
		
		$date = mktime(13,0,0,7,18,2010);
		$this->assertMethodReturn(60*60*24*31,array('MONTHLY',$date));
		$date = mktime(13,0,0,2,18,2010);
		$this->assertMethodReturn(60*60*24*28,array('MONTHLY',$date));
		$date = mktime(13,0,0,2,18,2012);
		$this->assertMethodReturn(60*60*24*29,array('MONTHLY',$date));
		
		$date = mktime(13,0,0,6,18,2010);
		$this->assertMethodReturn(60*60*24*365,array('YEARLY',$date));
		$date = mktime(13,0,0,6,18,2011);
		$this->assertMethodReturn(60*60*24*366,array('YEARLY',$date));
		$date = mktime(13,0,0,2,18,2012);
		$this->assertMethodReturn(60*60*24*366,array('YEARLY',$date));
	}
	
	public function test_next_bysecond() {
		$cdate = mktime(13,0,0,6,18,2010);
		
		$rrule = array('BYSECOND'=>array(3,5,27));
		$result = array(
			mktime(13,0,3,6,18,2010),
			mktime(13,0,5,6,18,2010),
			mktime(13,0,27,6,18,2010)
		);
		$this->assertMethodReturn($result,array($cdate,$rrule));
		
		$rrule = array('BYSECOND'=>array(-3,5,27));
		$result = array(
			mktime(13,0,57,6,18,2010),
			mktime(13,0,5,6,18,2010),
			mktime(13,0,27,6,18,2010)
		);
		$this->assertMethodReturn($result,array($cdate,$rrule));
		
		$cdate = array(mktime(13,0,0,6,18,2010),mktime(13,0,3,6,18,2011));
		$result = array(
			mktime(13,0,57,6,18,2010),mktime(13,0,57,6,18,2011),
			mktime(13,0,5,6,18,2010),mktime(13,0,5,6,18,2011),
			mktime(13,0,27,6,18,2010),mktime(13,0,27,6,18,2011)
		);
		$this->assertMethodReturn($result,array($cdate,$rrule));
	}
	
	public function test_next_byminute() {
		$cdate = mktime(13,0,0,6,18,2010);
		
		$rrule = array('BYMINUTE'=>array(3,5,27));
		$result = array(
			mktime(13,3,0,6,18,2010),
			mktime(13,5,0,6,18,2010),
			mktime(13,27,0,6,18,2010)
		);
		$this->assertMethodReturn($result,array($cdate,$rrule));
		
		$rrule = array('BYMINUTE'=>array(-3,5,27));
		$result = array(
			mktime(13,57,0,6,18,2010),
			mktime(13,5,0,6,18,2010),
			mktime(13,27,0,6,18,2010)
		);
		$this->assertMethodReturn($result,array($cdate,$rrule));
		
		$cdate = array(mktime(13,0,0,6,18,2010),mktime(13,3,0,6,18,2011));
		$result = array(
			mktime(13,57,0,6,18,2010),mktime(13,57,0,6,18,2011),
			mktime(13,5,0,6,18,2010),mktime(13,5,0,6,18,2011),
			mktime(13,27,0,6,18,2010),mktime(13,27,0,6,18,2011)
		);
		$this->assertMethodReturn($result,array($cdate,$rrule));
	}
	
	public function test_next_byhour() {
	}
	
	public function test_next_byday() {
	}
	
	public function test_next_bymonthday() {
	}
	
	public function test_next_byyearday() {
	}
	
	public function test_next_byweekno() {
	}
	
	public function test_next_bymonthy() {
	}
	
	public function test_is_leap_year() {
		$this->assertMethodReturnTrue(2000);
		$this->assertMethodReturnFalse(1900);
		$this->assertMethodReturnFalse(2011);
		$this->assertMethodReturnTrue(2012);
	}
	
	public function test_get_four_digit_year() {
		$this->assertMethodReturn(2000,2000);
		$this->assertMethodReturn(1978,78);
		$this->assertMethodReturn(2011,11);
	}
	
	public function test_make_array() {
		$this->assertMethodReturn(array('TEST'),'TEST');
		$this->assertMethodReturn(array('TEST'),array(array('TEST')));
	}
}