<?php
require_once('globals.php');

/**
 *  Unit Test for the Template class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends ImpactPHPUnit
 */
class Test_Template extends ImpactPHPUnit {
    private $acl;
    private $application;
    
    protected function setUp() {
        $this->init();
        
        $this->acl = $this->getMock('Acl',array('allowed'));
        $this->acl->expects($this->any())->method('allowed')->will($this->returnValue(true));
        $this->instance->init(array('acl'=>$this->acl));
    }
    
    public function test_parse() {
        // STUB
    }
    
    //$this->assertMethodReturn($expected,$args);
    public function test_convert_brackets_to_xml() {
        
        $this->assertMethodReturn(
            '<template:plugin name="date" />',
            '[[PLUGIN name="date"]]'
        );
        $this->assertMethodReturn(
            '<template:plugin name="date" format="Y-m-d\TH:i:s\Z" />',
            '[[plugin name="date" format="Y-m-d\TH:i:s\Z"]]'
        );
        $this->assertMethodReturn(
            '<template:feature name="christmas" />',
            '[[FEATURE name="christmas"]]'
        );
        $this->assertMethodReturn(
            '[[TEMPLATE name="main"]]',
            '[[TEMPLATE name="main"]]'
        );
        $this->assertMethodReturn(
            '<template:feature name="christmas" />'."\n".'<template:plugin name="date" />',
            '[[FEATURE name="christmas"]]'."\n".'[[PLUGIN name="date"]]'
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
        
        $this->assertMethodReturn(
            array(
                'block' => '<template:loop name="children"><p>TEST</p></template:loop>',
                'tagname' => 'loop',
                'attributes' => array('name'=>'children'),
                'content' => '<p>TEST</p>'
            ),array(array(
                '<template:loop name="children"><p>TEST</p></template:loop>',
                'loop',
                ' name="children"',
                '<p>TEST</p>'
            ))
	    );
        
        $this->assertMethodReturn(
            array(
                'block' => '<template:data name="node" notblank="parent" />',
                'tagname' => 'data',
                'attributes' => array('name'=>'node','notblank'=>'parent'),
                'content' => ''
            ),array(array(
                '<template:data name="node" notblank="parent" />',
                'data',
                ' name="node" notblank="parent"',
                ''
            ))
	    );
        
        $this->assertMethodReturn(
            array(
                'block' => 'template:variable[node]',
                'tagname' => 'variable',
                'attributes' => array(),
                'content' => 'node'
            ),array(array(
                'template:variable[node]',
                'variable',
                'node',
                ''
            ))
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
        
        $this->assertMethodReturn(
            array('id'=>'test', 'class'=>'bluebox'),
            '<div id="test" class="bluebox">TEST TEXT</div>'
		);
        $this->assertMethodReturn(
            array('id'=>'test', 'class'=>'bluebox'),
            '<div id=\'test\' class = "bluebox">TEST TEXT</div>'
          
		);
        $this->assertMethodReturn(
            array('id'=>'test', 'class'=>'bluebox'),
            '<div id ="test" class ="bluebox">TEST TEXT</div>'
           
	    );
         $this->assertMethodReturn(
            array('id'=>'test', 'class'=>'bluebox'),
			'<div   id="test"   class="bluebox"  >TEST TEXT</div>'
		);
    }
    
}