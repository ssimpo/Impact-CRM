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
class Test_Template_Variable extends PHPUnit_Framework_TestCase {
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
		if (!defined('MODELS_DIRECTORY')) {
			define('MODELS_DIRECTORY','models');
		}
		if (!defined('ROOT_BACK')) {
			define('ROOT_BACK',__DIR__.DS.'..'.DS.'..'.DS.'..'.DS);
		}
		spl_autoload_register('self::__autoload');
		
		$this->parser = new Template_Variable;
		$this->acl = $this->getMock('Acl',array('allowed'));
        $this->acl->expects($this->any())->method('allowed')->will($this->returnValue(true));
	}
	
	private function __autoload($className) {
		$classFileName = str_replace('_',DS,$className).'.php';
		require_once ROOT_BACK.MODELS_DIRECTORY.DS.$classFileName;
	}
	
	protected static function get_method($name) {
		$class = new ReflectionClass('Template_Variable');
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
	}
	
	public function test_parse() {
		$result1 = 'Impact';
        
        $this->parser->init(
            array('classname'=>'Impact', 'acl'=>$this->acl)
        );
        
        $data = array(
            'block'=>'','tagname'=>'variable','content'=>'classname',
            'attributes'=>array()
        );
        $this->assertEquals(
            $result1,
            $this->parser->parse($data)
        );
	}
}