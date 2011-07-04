<?php
require_once('globals.php');

/**
 *
 *  Unit Test for the ICalRRuleParser class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends ImpactPHPUnit
 */
class Test_ICalRRuleParser extends ImpactPHPUnit {

    protected function setUp() {
        $this->init();
    }
    
    public function test_parse() {
        // STUB
    }
    
    public function test_split_rrule() {
        $rrule = 'FREQ=WEEKLY;INTERVAL=2;WKST=SU';
        $result = array('FREQ'=>'WEEKLY','INTERVAL'=>2,'WKST'=>'SU');
        $this->assertMethodReturn($result,$rrule);
        
        $rrule = 'Freq=WEEKLY;INTERVAL=2;WKST=su';
        $result = array('FREQ'=>'WEEKLY','INTERVAL'=>2,'WKST'=>'SU');
        $this->assertMethodReturn($result,array($rrule));
        
        $rrule = 'FREQ=WEEKLY;UNTIL=19971007T000000Z;WKST=SU;BYDAY=TU,TH';
        $result = array(
            'FREQ'=>'WEEKLY','UNTIL'=>'19971007T000000Z','BYDAY'=>array('TU','TH'),'WKST'=>'SU'
        );
        $this->assertMethodReturn($result,$rrule);
        
        $rrule = 'FREQ=MONTHLY;COUNT=10;BYMONTHDAY=2,15';
        $result = array('FREQ'=>'MONTHLY','COUNT'=>10,'BYMONTHDAY'=>array(2,15));
        $this->assertMethodReturn($result,$rrule);
    }
    
    public function test_get_rrule_values() {
        $this->assertMethodReturn('WEEKLY','FREQ=WEEKLY');
        $this->assertMethodReturn(10,'COUNT=10');
        $this->assertMethodReturn(array(2,15),'BYMONTHDAY=2,15');
        $this->assertMethodReturn(array('TU','TH'),'BYDAY=TU,TH');
    }
    
    public function test_next_interval() {
        $cdate = new Calendar_DateTime(2011,6,27,18,55,0);
        
        $rrule = array('FREQ'=>'SECONDLY','INTERVAL'=>3);
        $this->assertMethodReturn(
            new Calendar_DateTime(2011,6,27,18,55,3),
            array($cdate,$rrule)
        );
        
        $rrule = array('FREQ'=>'MINUTELY','INTERVAL'=>2);
        $this->assertMethodReturn(
            new Calendar_DateTime(2011,6,27,18,57,0),
            array($cdate,$rrule)
        );
        
        $rrule = array('FREQ'=>'HOURLY','INTERVAL'=>5);
        $this->assertMethodReturn(
            new Calendar_DateTime(2011,6,27,23,55,0),
            array($cdate,$rrule)
        );
        
        $rrule = array('FREQ'=>'DAILY','INTERVAL'=>5);
        $this->assertMethodReturn(
            new Calendar_DateTime(2011,7,2,18,55,0),
            array($cdate,$rrule)
        );
        
        $rrule = array('FREQ'=>'WEEKLY','INTERVAL'=>2);
        $this->assertMethodReturn(
            new Calendar_DateTime(2011,7,11,18,55,0),
            array($cdate,$rrule)
        );
        
        $rrule = array('FREQ'=>'MONTHLY','INTERVAL'=>1);
        $this->assertMethodReturn(
            new Calendar_DateTime(2011,7,27,18,55,0),
            array($cdate,$rrule)
        );
        
        $rrule = array('FREQ'=>'YEARLY','INTERVAL'=>3);
        $this->assertMethodReturn(
            new Calendar_DateTime(2014,6,27,18,55,0),
            array($cdate,$rrule)
        );
    }
    
    public function test_get_next() {
        $cdate = new Calendar_DateTime(2011,6,27,18,55,0);
        
        $rrule = array('BYMONTH'=>array(7,8,9));
        $this->assertMethodReturn(
            array(
                new Calendar_DateTime(2011,7,27,18,55,0),
                new Calendar_DateTime(2011,8,27,18,55,0),
                new Calendar_DateTime(2011,9,27,18,55,0)
            ),
            array($cdate,$rrule)
        );
        
        $rrule = array('BYYEARDAY'=>array(178,8,-99));
        $this->assertMethodReturn(
            array(
                new Calendar_DateTime(2011,6,27,18,55,0),
                new Calendar_DateTime(2011,1,8,18,55,0),
                new Calendar_DateTime(2011,9,23,18,55,0)
            ),
            array($cdate,$rrule)
        );
        $cdate2 = new Calendar_DateTime(2012,6,27,18,55,0);
        $this->assertMethodReturn(
            array(
                new Calendar_DateTime(2012,6,26,18,55,0),
                new Calendar_DateTime(2012,1,8,18,55,0),
                new Calendar_DateTime(2012,9,23,18,55,0)
            ),
            array($cdate2,$rrule)
        );
        
       $rrule = array('BYWEEKNO'=>array(1,3,-1));
       $this->assertMethodReturn(
            array(
                new Calendar_DateTime(2011,1,3,18,55,0),
                new Calendar_DateTime(2011,1,17,18,55,0),
                new Calendar_DateTime(2011,12,26,18,55,0)
            ),
            array($cdate,$rrule)
        );
        
        $rrule = array('BYMONTHDAY'=>array(28,29,30));
        $this->assertMethodReturn(
            array(
                new Calendar_DateTime(2011,6,28,18,55,0),
                new Calendar_DateTime(2011,6,29,18,55,0),
                new Calendar_DateTime(2011,6,30,18,55,0)
            ),
            array($cdate,$rrule)
        );
        
        $rrule = array('BYHOUR'=>array(-1,23,17));
        $this->assertMethodReturn(
            array(
                new Calendar_DateTime(2011,6,27,23,55,0),
                new Calendar_DateTime(2011,6,27,23,55,0),
                new Calendar_DateTime(2011,6,27,17,55,0)
            ),
            array($cdate,$rrule)
        );
        
        $rrule = array('BYMINUTE'=>array(15,25,30));
        $this->assertMethodReturn(
            array(
                new Calendar_DateTime(2011,6,27,18,15,0),
                new Calendar_DateTime(2011,6,27,18,25,0),
                new Calendar_DateTime(2011,6,27,18,30,0)
            ),
            array($cdate,$rrule)
        );
        
        $rrule = array('BYSECOND'=>array(15,25,30));
        $this->assertMethodReturn(
            array(
                new Calendar_DateTime(2011,6,27,18,55,15),
                new Calendar_DateTime(2011,6,27,18,55,25),
                new Calendar_DateTime(2011,6,27,18,55,30)
            ),
            array($cdate,$rrule)
        );
    }
    
    public function test_intialize_rrule() {
    }
    
    public function test_sort_dates() {
        $date1 = new Calendar_DateTime(2009,6,27,18,45,0);
        $date2 = new Calendar_DateTime(2010,6,27,18,45,0);
        
        $this->assertMethodReturn(-1,array($date1,$date2));
        $this->assertMethodReturn(1,array($date2,$date1));
        $this->assertMethodReturn(0,array($date1,$date1));
    }
    
    public function test_make_array() {
        $this->assertMethodReturn(array('TEST'), 'TEST');
        $this->assertMethodReturn(array('TEST'), array(array('TEST')));
    }

    
    public function test_get_string_or_number() {
        $this->assertMethodReturn(10, '10');
        $this->assertMethodReturn('ten', 'ten');
        $this->assertMethodReturn(10.2, '10.2');
        $this->assertMethodReturn('10.com', '10.com');
    }
    
    public function test_contains() {
        $this->assertMethodReturnTrue(array('Impact Project','act'));
        $this->assertMethodReturnTrue(array('IMPACT Project','act'));
        $this->assertMethodReturnFalse(array('IMPACT Project','actt'));
    }
    
}