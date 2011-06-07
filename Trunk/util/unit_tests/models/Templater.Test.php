<?php
/**
 *	Unit Test for the Templater class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends PHPUnit_Framework_TestCase
 */
class Test_Templater extends PHPUnit_Framework_TestCase {
    private $templater = null;
    private $acl;
    private $application;
    
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
        spl_autoload_register('self::__autoload');
        
        $this->templater = new Templater;
        $this->acl = $this->getMock('Acl',array('allowed'));
        $this->acl->expects($this->any())->method('allowed')->will($this->returnValue(true));
    }
    
    private function __autoload($className) {
        $classFileName = str_replace('_',DS,$className).'.php';
        require_once ROOT_BACK.MODELS_DIRECTORY.DS.$classFileName;
    }
    
    protected static function get_method($name) {
        $class = new ReflectionClass('Templater');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
    
    public function test_parse() {
        // STUB
    }
    
    public function test_convert_brackets_to_xml() {
        $method = self::get_method('_convert_brackets_to_xml');
        
        $text = '[[PLUGIN name="date"]]';
        $this->assertEquals(
            '<template:plugin name="date" />',
            $method->invokeArgs($this->templater,array($text))
        );
        
        $text = '[[plugin name="date" format="Y-m-d\TH:i:s\Z"]]';
        $this->assertEquals(
            '<template:plugin name="date" format="Y-m-d\TH:i:s\Z" />',
            $method->invokeArgs($this->templater,array($text))
        );
        
        $text = '[[FEATURE name="christmas"]]';
        $this->assertEquals(
            '<template:feature name="christmas" />',
            $method->invokeArgs($this->templater,array($text))
        );
        
        $text = '[[TEMPLATE name="main"]]';
        $this->assertEquals(
            $text,
            $method->invokeArgs($this->templater,array($text))
        );
        
        $text = '[[FEATURE name="christmas"]]'."\n".'[[PLUGIN name="date"]]';
        $this->assertEquals(
            '<template:feature name="christmas" />'."\n".'<template:plugin name="date" />',
            $method->invokeArgs($this->templater,array($text))
        );
    }
    
    public function test_parse_blocks() {
        $method = self::get_method('_parse_blocks');
        
        // STUB
    }
    
    public function test_parse_loops() {
        $method = self::get_method('_parse_loops');
        
        // STUB
    }
    
    public function test_parse_variables_and_constants() {
        $method = self::get_method('_parse_variables_and_constants');
        
        $application = array(
            'acl' => '',
            'component' => 'main',
            'testvar1' => 1,
            'testvar2' => 'HELLO WORLD',
            'testvar3' => 'HELLO AGAIN!'
        );
        $application2 = array(
            'acl' => '',
            'component' => 'main',
            'testvar4' => 'ONE MORE TIME'
        );
        
        $this->templater->init($application,'');
        
        $xml = '<table><tr><th>Total:</th><td>template:variable[testvar1]</td></tr></table>';
        $this->assertEquals(
            '<table><tr><th>Total:</th><td>1</td></tr></table>',
            $method->invokeArgs($this->templater,array($xml))
        );
        
        $xml = '<table><tr><th>Greeting:</th><td>template:variable[testvar2]</td></tr></table>';
        $this->assertEquals(
            '<table><tr><th>Greeting:</th><td>HELLO WORLD</td></tr></table>',
            $method->invokeArgs($this->templater,array($xml))
        );
        
        $path = ROOT_BACK.'util'.DS.'unit_tests'.DS.'models'.DS.'_data'.DS.'xml'.DS.'Template.2.Test.xml';
        $xml1=file_get_contents($path);
        $path = ROOT_BACK.'util'.DS.'unit_tests'.DS.'models'.DS.'_data'.DS.'xml'.DS.'Template.3.Test.xml';
        $xml2=file_get_contents($path);
        $this->assertEquals(
            $xml2,
            $method->invokeArgs($this->templater,array($xml1))
        );
        
        define('DOMAIN','www.test.com');
        $xml = '<table><tr><th>Greeting:</th><td>template:constant[DOMAIN]</td></tr></table>';
        $this->assertEquals(
            '<table><tr><th>Greeting:</th><td>www.test.com</td></tr></table>',
            $method->invokeArgs($this->templater,array($xml))
        );
        
        $this->templater->init($application,$application2,'');
        $xml = '<table><tr><th>Greeting:</th><td>template:variable[testvar4]</td></tr></table>';
        $this->assertEquals(
            '<table><tr><th>Greeting:</th><td>ONE MORE TIME</td></tr></table>',
            $method->invokeArgs($this->templater,array($xml))
        );
    }
    
    public function test_parse_templates() {
        $method = self::get_method('_parse_templates');
        
        // STUB
    }
    
    public function test_get_xml() {
        $method = self::get_method('_get_xml');
        
        $xml = '<html><head><title>TEST</title></head><body><h1>TEST</h1></body></html>';
        $method->invokeArgs($this->templater,array($xml));
        $this->assertEquals($this->templater->xml,$xml);
        
        $xml = '
        <html>
            <head>
                <title>TEST</title>
            </head>
            <body>
                <h1>TEST</h1>
            </body>
        </html>';
        $method->invokeArgs($this->templater,array($xml));
        $this->assertEquals($this->templater->xml,$xml);
        
        $path = ROOT_BACK.'util'.DS.'unit_tests'.DS.'models'.DS.'_data'.DS.'xml'.DS.'Template.1.Test.xml';
        $xml=file_get_contents($path);
        $method->invokeArgs($this->templater,array($xml));
        $this->assertEquals($this->templater->xml,$xml);
    }
    
    public function test_loop() {
        $method = self::get_method('_loop');
        $result1 = '<p>TEST</p><p>TEST</p><p>TEST</p><p>TEST</p>';
        $result2 = '<ul><li>TEST</li><li>TEST</li></ul><ul><li>TEST</li><li>TEST</li></ul>';
        $result3 = '<p>TEST one</p><p>TEST two</p>';
        $matches1 = array('','','<p>TEST</p>');
        
        // Test against an item within the application array
        $this->templater->init(
            array('items'=>array(1,2,3,4), 'acl'=>$this->acl)
        );
        $matches1[1] = ' name="items"';
        $this->assertEquals(
            $result1,
            $method->invokeArgs($this->templater,array($matches1))
        );
        
        // Test against an array
        $this->templater->init(
            array(1,2,3,4), array('acl'=>$this->acl)
        );
        $matches1[1] = '';
        $this->assertEquals(
            $result1,
            $method->invokeArgs($this->templater,array($matches1))
        );
        
        // Test against loops of loops
        $this->templater->init(
            array(
                array('children'=>array(1,2)),
                array('children'=>array(1,2))
            ),
            array('acl'=>$this->acl)
        );
        $matches1[2] = '<ul><template:loop name="children"><li>TEST</li></template:loop></ul>';
        $this->assertEquals(
            $result2,
            $method->invokeArgs($this->templater,array($matches1))
        );
        
        // Test that data is transfered to the loop
        $this->templater->init(
            array(array('name'=>'one'),array('name'=>'two')),
            array('acl'=>$this->acl)
        );
        $matches1[2] = '<p>TEST <template:data name="name" /></p>';
        $this->assertEquals(
            $result3,
            $method->invokeArgs($this->templater,array($matches1))
        );
    }
    
    public function test_block() {
        $method = self::get_method('_block');
        
        // STUB
    }
    
    public function test_template() {
        $method = self::get_method('_template');
        
        // STUB
    }
    
    public function test_data() {
        $method = self::get_method('_data');
        $result1 = 'HELLO WORLD';
        
        $this->templater->init(
            array('data'=>'HELLO WORLD', 'acl'=>$this->acl)
        );
        
        $this->assertEquals(
            $result1,
            $method->invokeArgs($this->templater,array(' name="data"'))
        );
    }
    
    public function test_include() {
        $method = self::get_method('_include');
        
        // STUB
    }
    
    public function test_feature() {
        $method = self::get_method('_feature');
        
        // STUB
    }
    
    public function test_feature_loader() {
        $method = self::get_method('_feature_loader');
        
        // STUB
    }
    
    public function test_plugin() {
        $method = self::get_method('_plugin');
        
        // STUB
    }
    
    public function test_notblank() {
        $method = self::get_method('_notblank');
        
        // STUB
    }
    
    public function test_acl() {
        $method = self::get_method('_acl');
        
        // STUB
    }
    
    public function test_ical() {
        $method = self::get_method('_ical');
        
        // STUB
    }
    
    public function test_variable() {
        $method = self::get_method('_variable');
        
        // STUB
    }
    
    public function test_get_attributes() {
        $method = self::get_method('_get_attributes');
        
        $this->assertEquals(
            array('id'=>'test', 'class'=>'bluebox'),
			$method->invokeArgs(
                $this->templater,
                array('<div id="test" class="bluebox">TEST TEXT</div>')
            )
		);
        
        $this->assertEquals(
            array('id'=>'test', 'class'=>'bluebox'),
			$method->invokeArgs(
                $this->templater,
                array('<div id=\'test\' class = "bluebox">TEST TEXT</div>')
            )
		);
        
        $this->assertEquals(
            array('id'=>'test', 'class'=>'bluebox'),
			$method->invokeArgs(
                $this->templater,
                array('<div id ="test" class ="bluebox">TEST TEXT</div>')
            )
		);
        
         $this->assertEquals(
            array('id'=>'test', 'class'=>'bluebox'),
			$method->invokeArgs(
                $this->templater,
                array('<div   id="test"   class="bluebox"  >TEST TEXT</div>')
            )
		);
    }
    
    public function test_date_reformat() {
        $method = self::get_method('_date_reformat');
        
        $this->assertEquals(
            '20110427T211453Z',
			$method->invokeArgs($this->templater,array('2011-04-27 21:14:53'))
		);
    }
    
    public function test_contains() {
        $method = self::get_method('_contains');
        
        $this->assertTrue(
			$method->invokeArgs(
                $this->templater,
                array('<template:test />','>')
            )
		);
        $this->assertFalse(
			$method->invokeArgs(
                $this->templater,
                array('<template:test />','_')
            )
		);
        $this->assertTrue(
			$method->invokeArgs(
                $this->templater,
                array('<template:test />','T')
            )
		);
    }
    
}