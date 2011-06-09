<?php
/**
 *	Unit Test for the Database class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends PHPUnit_Framework_TestCase
 */
class Test_Database_SqlSequencer extends PHPUnit_Framework_TestCase {
    private $database = null;
    
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
		if (!defined('DB_DRIVER')) {
			define('DB_DRIVER','SQLITE');
		}
		if (!defined('CACHE_DIRECTORY')) {
			define('CACHE_DIRECTORY','cache/');
		}
		if (!defined('DB_NAME')) {
			define('DB_NAME','database/impact.db#');
		}
		spl_autoload_register('self::__autoload');
	
		$this->database = new Database_SqlSequencer;
    }
    
    private function __autoload($className) {
		$classFileName = str_replace('_',DS,$className).'.php';
		require_once ROOT_BACK.MODELS_DIRECTORY.DS.$classFileName;
    }
	
	protected static function get_method($name) {
		$class = new ReflectionClass('Database_SqlSequencer');
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
    }
	
	public function test_make_array() {
		$method = self::get_method('_make_array');
		
		$this->assertEquals(
            array('item1'),
            $method->invokeArgs($this->database,array('item1'))
        );
		
		$this->assertEquals(
            array('item1','item2'),
            $method->invokeArgs(
				$this->database,
				array(array('item1','item2'))
			)
        );
	}
	
	public function test_make_array_of_array() {
		$method = self::get_method('_make_array_of_array');
		
		$this->assertEquals(
            array(array('item1')),
            $method->invokeArgs(
				$this->database,
				array('item1')
			)
        );
		
		$this->assertEquals(
            array(array('item1'),array('item2')),
            $method->invokeArgs(
				$this->database,
				array(array('item1','item2'))
			)
        );
		
		$this->assertEquals(
            array(
				array('item1','item2'), array('item1','item2')
			),
            $method->invokeArgs(
				$this->database,
				array(array(
					array('item1','item2'), array('item1','item2')
				))
			)
        );
		
		$this->assertEquals(
            array(
				array('item1'), array('item1','item2')
			),
            $method->invokeArgs(
				$this->database,
				array(array(
					'item1', array('item1','item2')
				))
			)
        );
	}
	
	public function test_matrix_size() {
		$method = self::get_method('_matrix_size');
		
		$this->database->values = array(
			array(1,2,3,4,5,6), array(1,2,3), array(1,2)
		);
		$this->assertEquals(36,$method->invokeArgs($this->database,array()));
		
		$this->database->values = array(
			array(1,2), array(1,2,3,4), array(1,2,3), array(1,2)
		);
		$this->assertEquals(48,$method->invokeArgs($this->database,array()));
	}
	
	public function test_create_blank_matrix() {
		$method = self::get_method('_create_blank_matrix');
		$this->database->entities = array('entity1','entity2','entity3');
		$this->database->values = array(array(1,2,3));
		
		$this->assertEquals(
			array(
				array('entity1'=>'','entity2'=>'','entity3'=>''),
				array('entity1'=>'','entity2'=>'','entity3'=>''),
				array('entity1'=>'','entity2'=>'','entity3'=>'')
			),
            $method->invokeArgs($this->database,array())
        );
	}
	
	public function test_create_blank_row() {
		$method = self::get_method('_create_blank_row');
		
		$this->database->entities = array('entity1','entity2','entity3');
		$this->assertEquals(
            array('entity1'=>'','entity2'=>'','entity3'=>''),
            $method->invokeArgs($this->database,array())
        );
	}
	
	public function test_calc_repeat_number() {
		$method = self::get_method('_calc_repeat_number');
		
		$this->database->values = array(
			array('en_gb','en_us'),
			array('PC','FACEBOOK','MOBILE'),
			array('ADMIN','WEB','SUPERUSER')
		);
		
		$this->assertEquals(
            1, $method->invokeArgs($this->database,array(0))
        );
		
		$this->assertEquals(
            2, $method->invokeArgs($this->database,array(1))
        );
		
		$this->assertEquals(
            6, $method->invokeArgs($this->database,array(2))
        );
		
	}
	
	public function test_create_matrix() {
		$method = self::get_method('_create_matrix');
		
		$this->database->entities = array('<LANG>');
		$this->database->values = array(array('en_gb','en_us'));
		$this->assertEquals(
            array(array('<LANG>'=>'en_gb'), array('<LANG>'=>'en_us')),
            $method->invokeArgs($this->database,array())
        );
		
		$this->database->entities = array('<LANG>','<MEDIA>');
		$this->database->values = array(
			array('en_gb','en_us'), array('PC','FACEBOOK','MOBILE')
		);
		$this->assertEquals(
            array(
				array('<LANG>'=>'en_gb','<MEDIA>'=>'PC'),
				array('<LANG>'=>'en_us','<MEDIA>'=>'PC'),
				array('<LANG>'=>'en_gb','<MEDIA>'=>'FACEBOOK'),
				array('<LANG>'=>'en_us','<MEDIA>'=>'FACEBOOK'),
				array('<LANG>'=>'en_gb','<MEDIA>'=>'MOBILE'),
				array('<LANG>'=>'en_us','<MEDIA>'=>'MOBILE')
			),
            $method->invokeArgs($this->database,array())
        );
		
		$this->database->entities = array('<LANG>','<MEDIA>','<ACCESS>');
		$this->database->values = array(
			array('en_gb','en_us'),
			array('PC','FACEBOOK','MOBILE'),
			array('ADMIN','WEB')
		);
		$this->assertEquals(
            array(
				array('<LANG>'=>'en_gb','<MEDIA>'=>'PC','<ACCESS>'=>'ADMIN'),
				array('<LANG>'=>'en_us','<MEDIA>'=>'PC','<ACCESS>'=>'ADMIN'),
				array('<LANG>'=>'en_gb','<MEDIA>'=>'FACEBOOK','<ACCESS>'=>'ADMIN'),
				array('<LANG>'=>'en_us','<MEDIA>'=>'FACEBOOK','<ACCESS>'=>'ADMIN'),
				array('<LANG>'=>'en_gb','<MEDIA>'=>'MOBILE','<ACCESS>'=>'ADMIN'),
				array('<LANG>'=>'en_us','<MEDIA>'=>'MOBILE','<ACCESS>'=>'ADMIN'),
				array('<LANG>'=>'en_gb','<MEDIA>'=>'PC','<ACCESS>'=>'WEB'),
				array('<LANG>'=>'en_us','<MEDIA>'=>'PC','<ACCESS>'=>'WEB'),
				array('<LANG>'=>'en_gb','<MEDIA>'=>'FACEBOOK','<ACCESS>'=>'WEB'),
				array('<LANG>'=>'en_us','<MEDIA>'=>'FACEBOOK','<ACCESS>'=>'WEB'),
				array('<LANG>'=>'en_gb','<MEDIA>'=>'MOBILE','<ACCESS>'=>'WEB'),
				array('<LANG>'=>'en_us','<MEDIA>'=>'MOBILE','<ACCESS>'=>'WEB'),
			),
            $method->invokeArgs($this->database,array())
        );
	}
}