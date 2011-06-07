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
    
}