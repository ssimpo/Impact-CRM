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
    
    public function test_factory() {
        $test = 'WEEKLY';
        $this->assertTrue(
            get_class($this->instance->factory($test)) == 'ICalRRuleParser_Weekly'
        );
        
        $test = array('FREQ'=>'WEEKLY','INTERVAL'=>2,'WKST'=>'SU');
        $this->assertTrue(
            get_class($this->instance->factory($test)) == 'ICalRRuleParser_Weekly'
        );
        
        $test = array('FREQ'=>'MONTHLY','COUNT'=>10,'BYMONTHDAY'=>array(2,15));
        $this->assertTrue(
            get_class($this->instance->factory($test)) == 'ICalRRuleParser_Monthly'
        );
        
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