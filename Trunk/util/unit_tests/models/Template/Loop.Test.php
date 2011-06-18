<?php
require_once('globals.php');

/**
 *	Unit Test for the Template class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends ImpactPHPUnit
 */
class Test_Template_Loop extends ImpactPHPUnit {
    private $acl;
	
	protected function setUp() {
		$this->init();
		$this->acl = $this->getMock('Acl',array('allowed'));
		$this->acl->expects($this->any())->method('allowed')->will($this->returnValue(true));
	}
	
	public function test_parse() {
        $result1 = '<p>TEST</p><p>TEST</p><p>TEST</p><p>TEST</p>';
        $result2 = '<ul><li>TEST</li><li>TEST</li></ul><ul><li>TEST</li><li>TEST</li></ul>';
        $result3 = '<p>TEST one</p><p>TEST two</p>';
		$match = array(
			'block' => '',
			'tagname' => 'loop',
			'attributes' => array(),
			'content' => '<p>TEST</p>'
		);
		
        // Test against an item within the application array
        $this->instance->init(
            array('items'=>array(1,2,3,4), 'acl'=>$this->acl)
        );
        $match['attributes'] = array('name'=>'items');
        $this->assertEquals(
            $result1,
            $this->instance->parse($match)
        );
        
        // Test against an array
        $this->instance->init(
            array(1,2,3,4), array('acl'=>$this->acl)
        );
        $match['attributes'] = array();
        $this->assertEquals(
			$result1,
			$this->instance->parse($match)
        );
        
        // Test against nested loops
        $this->instance->init(
            array(
                array('children'=>array(1,2)),
                array('children'=>array(1,2))
            ),
            array('acl'=>$this->acl)
        );
        $match['content'] = '<ul><template:loop name="children"><li>TEST</li></template:loop></ul>';
        $this->assertEquals(
            $result2,
			$this->instance->parse($match)
        );
        
        // Test that data is transferred to the loop
        $this->instance->init(
            array(array('name'=>'one'),array('name'=>'two')),
            array('acl'=>$this->acl)
        );
        $match['content'] = '<p>TEST <template:data name="name" /></p>';
        $this->assertEquals(
            $result3,
            $this->instance->parse($match)
        );
	}
}