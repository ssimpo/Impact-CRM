<?php
/**
 *	  Unit Test for the Acl class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends PHPUnit_Framework_TestCase
 */
class Test_Templater_Loop extends PHPUnit_Framework_TestCase {
    private $parser;
	private $acl;
	
    protected function setUp() {
		if (!defined('__DIR__')) {
			$iPos = strrpos(__FILE__, "/");
			define('__DIR__', substr(__FILE__, 0, $iPos) . '/');
		}
		if (!defined('DS')) {
			define('DS',DIRECTORY_SEPARATOR);
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
		
		$this->parser = new Templater_Loop;
		$this->acl = $this->getMock('Acl',array('allowed'));
        $this->acl->expects($this->any())->method('allowed')->will($this->returnValue(true));
	}
	
	private function __autoload($className) {
		$classFileName = str_replace('_',DS,$className).'.php';
		require_once ROOT_BACK.MODELS_DIRECTORY.DS.$classFileName;
	}
	
	protected static function get_method($name) {
		$class = new ReflectionClass('Template_Loop');
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
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
        $this->parser->init(
            array('items'=>array(1,2,3,4), 'acl'=>$this->acl)
        );
        $match['attributes'] = array('name'=>'items');
        $this->assertEquals(
            $result1,
            $this->parser->parse($match)
        );
        
        // Test against an array
        $this->parser->init(
            array(1,2,3,4), array('acl'=>$this->acl)
        );
        $match['attributes'] = array();
        $this->assertEquals(
			$result1,
			$this->parser->parse($match)
        );
        
        // Test against nested loops
        $this->parser->init(
            array(
                array('children'=>array(1,2)),
                array('children'=>array(1,2))
            ),
            array('acl'=>$this->acl)
        );
        $match['content'] = '<ul><template:loop name="children"><li>TEST</li></template:loop></ul>';
        $this->assertEquals(
            $result2,
			$this->parser->parse($match)
        );
        
        // Test that data is transfered to the loop
        $this->parser->init(
            array(array('name'=>'one'),array('name'=>'two')),
            array('acl'=>$this->acl)
        );
        $match['content'] = '<p>TEST <template:data name="name" /></p>';
        $this->assertEquals(
            $result3,
            $this->parser->parse($match)
        );
	}
}