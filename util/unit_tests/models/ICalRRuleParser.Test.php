<?php
/**
 *	Unit Test for the Template class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends PHPUnit_Framework_TestCase
 */
class Test_ICalRRuleParser extends PHPUnit_Framework_TestCase {
    private $parser;
    
    protected function setUp() {
        if (!defined('__DIR__')) {
            $iPos = strrpos(__FILE__, "/");
            define('__DIR__', substr(__FILE__, 0, $iPos) . '/');
        }
        if (!defined('DS')) {
            define('DS',DIRECTORY_SEPARATOR);
        }
        if (!defined('MODELS_DIRECTORY')) {
            define('MODELS_DIRECTORY','models');
        }
        if (!defined('ROOT_BACK')) {
            define('ROOT_BACK',__DIR__.DS.'..'.DS.'..'.DS.'..'.DS);
        }
        if (!defined('DIRECT_ACCESS_CHECK')) {
            define('DIRECT_ACCESS_CHECK',false);
        }
        if (!defined('USE_LOCAL_MODELS')) {
            define('USE_LOCAL_MODELS',false);
        }
        spl_autoload_register('self::__autoload');
        
        $this->parser = new ICalRRuleParser;
    }
    
    private function __autoload($className) {
        $classFileName = str_replace('_',DS,$className).'.php';
        require_once ROOT_BACK.MODELS_DIRECTORY.DS.$classFileName;
    }
    
    protected static function get_method($name) {
        $class = new ReflectionClass('ICalRRuleParser');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
    
    public function test_parse() {
        // STUB
    }
    
    public function test_factory() {
        $test = 'WEEKLY';
        $this->assertTrue(
            get_class($this->parser->factory($test)) == 'ICalRRuleParser_Weekly'
        );
        
        $test = array('FREQ'=>'WEEKLY','INTERVAL'=>2,'WKST'=>'SU');
        $this->assertTrue(
            get_class($this->parser->factory($test)) == 'ICalRRuleParser_Weekly'
        );
        
        $test = array('FREQ'=>'MONTHLY','COUNT'=>10,'BYMONTHDAY'=>array(2,15));
        $this->assertTrue(
            get_class($this->parser->factory($test)) == 'ICalRRuleParser_Monthly'
        );
        
    }
    
    public function test_split_rule() {
        $method = self::get_method('_split_rrule');
        
        $rrule = 'FREQ=WEEKLY;INTERVAL=2;WKST=SU';
        $result = array('FREQ'=>'WEEKLY','INTERVAL'=>2,'WKST'=>'SU');
        $this->assertEquals(
            $result, $method->invokeArgs($this->parser,array($rrule))
        );
        
        $rrule = 'Freq=WEEKLY;INTERVAL=2;WKST=su';
        $result = array('FREQ'=>'WEEKLY','INTERVAL'=>2,'WKST'=>'SU');
        $this->assertEquals(
            $result, $method->invokeArgs($this->parser,array($rrule))
        );
        
        $rrule = 'FREQ=WEEKLY;UNTIL=19971007T000000Z;WKST=SU;BYDAY=TU,TH';
        $result = array(
            'FREQ'=>'WEEKLY','UNTIL'=>'19971007T000000Z','BYDAY'=>array('TU','TH'),'WKST'=>'SU'
        );
        $this->assertEquals(
            $result, $method->invokeArgs($this->parser,array($rrule))
        );
        
        $rrule = 'FREQ=MONTHLY;COUNT=10;BYMONTHDAY=2,15';
        $result = array('FREQ'=>'MONTHLY','COUNT'=>10,'BYMONTHDAY'=>array(2,15));
        $this->assertEquals(
            $result, $method->invokeArgs($this->parser,array($rrule))
        );
    }
    
    public function test_get_rrule_values() {
        $method = self::get_method('_get_rrule_values');
        
        $rrule = 'FREQ=WEEKLY';
        $result = 'WEEKLY';
        $this->assertEquals(
            $result, $method->invokeArgs($this->parser,array($rrule))
        );
        
        $rrule = 'COUNT=10';
        $result = 10;
        $this->assertEquals(
            $result, $method->invokeArgs($this->parser,array($rrule))
        );
        
        $rrule = 'BYMONTHDAY=2,15';
        $result = array(2,15);
        $this->assertEquals(
            $result, $method->invokeArgs($this->parser,array($rrule))
        );
        
        $rrule = 'BYDAY=TU,TH';
        $result = array('TU','TH');
        $this->assertEquals(
            $result, $method->invokeArgs($this->parser,array($rrule))
        );
    }
    
    public function test_get_string_or_number() {
        $method = self::get_method('_get_string_or_number');
        
        $this->assertEquals(10, $method->invokeArgs($this->parser,array('10')));
        $this->assertEquals('ten', $method->invokeArgs($this->parser,array('ten')));
        $this->assertEquals(10.2, $method->invokeArgs($this->parser,array('10.2')));
        $this->assertEquals('10.com', $method->invokeArgs($this->parser,array('10.com')));
    }
    
    public function test_contains() {
        $method = self::get_method('_contains');
        
        $this->assertTrue($method->invokeArgs($this->parser,array('Impact Project','act')));
	$this->assertTrue($method->invokeArgs($this->parser,array('IMPACT Project','act')));
	$this->assertFalse($method->invokeArgs($this->parser,array('IMPACT Project','actt')));
    }
    
}