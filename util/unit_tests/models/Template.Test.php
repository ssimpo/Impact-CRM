<?php
require_once('globals.php');

/**
 *	Unit Test for the Template class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends PHPUnit_Framework_TestCase
 */
class Test_Template extends ImpactPHPUnit {
    private $acl;
    private $application;
    
    protected function setUp() {
        $this->init('Template');
        
        $this->acl = $this->getMock('Acl',array('allowed'));
        $this->acl->expects($this->any())->method('allowed')->will($this->returnValue(true));
        $this->instance->init(array('acl'=>$this->acl));
    }
    
    public function test_parse() {
        // STUB
    }
    
    public function test_convert_brackets_to_xml() {
        $method = self::get_method('_convert_brackets_to_xml');
        
        $text = '[[PLUGIN name="date"]]';
        $this->assertEquals(
            '<template:plugin name="date" />',
            $method->invokeArgs($this->instance,array($text))
        );
        
        $text = '[[plugin name="date" format="Y-m-d\TH:i:s\Z"]]';
        $this->assertEquals(
            '<template:plugin name="date" format="Y-m-d\TH:i:s\Z" />',
            $method->invokeArgs($this->instance,array($text))
        );
        
        $text = '[[FEATURE name="christmas"]]';
        $this->assertEquals(
            '<template:feature name="christmas" />',
            $method->invokeArgs($this->instance,array($text))
        );
        
        $text = '[[TEMPLATE name="main"]]';
        $this->assertEquals(
            $text,
            $method->invokeArgs($this->instance,array($text))
        );
        
        $text = '[[FEATURE name="christmas"]]'."\n".'[[PLUGIN name="date"]]';
        $this->assertEquals(
            '<template:feature name="christmas" />'."\n".'<template:plugin name="date" />',
            $method->invokeArgs($this->instance,array($text))
        );
    }
    
    public function test_get_xml() {
        $method = self::get_method('_get_xml');
        
        $xml = '<html><head><title>TEST</title></head><body><h1>TEST</h1></body></html>';
        $method->invokeArgs($this->instance,array($xml));
        $this->assertEquals($this->instance->xml,$xml);
        
        $xml = '
        <html>
            <head>
                <title>TEST</title>
            </head>
            <body>
                <h1>TEST</h1>
            </body>
        </html>';
        $method->invokeArgs($this->instance,array($xml));
        $this->assertEquals($this->instance->xml,$xml);
        
        $path = ROOT_BACK.'util'.DS.'unit_tests'.DS.'models'.DS.'_data'.DS.'xml'.DS.'Template.1.Test.xml';
        $xml=file_get_contents($path);
        $method->invokeArgs($this->instance,array($xml));
        $this->assertEquals($this->instance->xml,$xml);
    }
    
    public function test_create_match_array() {
        $method = self::get_method('_create_match_array');
        
        $this->assertEquals(
            array(
                'block' => '<template:loop name="children"><p>TEST</p></template:loop>',
                'tagname' => 'loop',
                'attributes' => array('name'=>'children'),
                'content' => '<p>TEST</p>'
            ),
            $method->invokeArgs(
                $this->instance,
                array(array(
                    '<template:loop name="children"><p>TEST</p></template:loop>',
                    'loop',
                    ' name="children"',
                    '<p>TEST</p>'
                ))
            )
	    );
        
        $this->assertEquals(
            array(
                'block' => '<template:data name="node" notblank="parent" />',
                'tagname' => 'data',
                'attributes' => array('name'=>'node','notblank'=>'parent'),
                'content' => ''
            ),
            $method->invokeArgs(
                $this->instance,
                array(array(
                    '<template:data name="node" notblank="parent" />',
                    'data',
                    ' name="node" notblank="parent"',
                    ''
                ))
            )
	    );
        
        $this->assertEquals(
            array(
                'block' => 'template:variable[node]',
                'tagname' => 'variable',
                'attributes' => array(),
                'content' => 'node'
            ),
            $method->invokeArgs(
                $this->instance,
                array(array(
                    'template:variable[node]',
                    'variable',
                    'node',
                    ''
                ))
            )
	    );
    }
    
    public function test_get_parser() {
        $method = self::get_method('_get_parser');
        
        $this->assertEquals(
            'Template_Loop',
            get_class(
                $method->invokeArgs($this->instance,array('Template_Loop'))
            )
	    );
    }
    
    public function test_get_attributes() {
        $method = self::get_method('_get_attributes');
        
        $this->assertEquals(
            array('id'=>'test', 'class'=>'bluebox'),
            $method->invokeArgs(
                $this->instance,
                array('<div id="test" class="bluebox">TEST TEXT</div>')
            )
		);
        
        $this->assertEquals(
            array('id'=>'test', 'class'=>'bluebox'),
            $method->invokeArgs(
                $this->instance,
                array('<div id=\'test\' class = "bluebox">TEST TEXT</div>')
            )
		);
        
        $this->assertEquals(
            array('id'=>'test', 'class'=>'bluebox'),
            $method->invokeArgs(
                $this->instance,
                array('<div id ="test" class ="bluebox">TEST TEXT</div>')
            )
	    );
        
         $this->assertEquals(
            array('id'=>'test', 'class'=>'bluebox'),
			$method->invokeArgs(
                $this->instance,
                array('<div   id="test"   class="bluebox"  >TEST TEXT</div>')
            )
		);
    }
    
}