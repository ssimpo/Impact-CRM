<?php
require_once('globals.php');

/**
 *	Unit Test for the Filesystem_Path class.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html
 *	@package UnitTests.Impact
 *	@extends ImpactPHPUnit
 */
class Test_Filesystem_Path extends ImpactPHPUnit {
	
	protected function setUp() {
        $this->init();
	}
	
	public function test_remove_set_fullpath() {
		// STUB
	}
	
	public function test_set_fullpath_local() {
		$this->instance->set_path('/usr/simpo/docs/personal.doc');
		$this->assertMethodReturn(DS.'usr'.DS.'simpo'.DS.'docs'.DS.'personal.doc');
		$this->instance->set_path('c:\\Program Files\\PHP\\php.exe');
		$this->assertMethodReturn('c:'.DS.'Program Files'.DS.'PHP'.DS.'php.exe');
	}
	
	public function test_set_fullpath_url() {
		$this->instance->set_path('http://www.impact-crm.com/test/index.htm');
		$this->assertMethodReturn('http://www.impact-crm.com/test/index.htm');
		$this->instance->set_path('http://www.impact-crm.com/test/index.htm#test');
		$this->assertMethodReturn('http://www.impact-crm.com/test/index.htm#test');
		$this->instance->set_path('ftp://test:test@www.impact-crm.com/help');
		$this->assertMethodReturn('ftp://test:test@www.impact-crm.com/help');
		$this->instance->set_path('http://www.impact-crm.com/test/?test1=value1&open');
		$this->assertMethodReturn('http://www.impact-crm.com/test/?test1=value1&amp;open');
		$this->instance->set_path('file:///c:/Program Files/PHP/php.exe');
		$this->assertMethodReturn('file:///c:/Program Files/PHP/php.exe');
	}
	
	public function test_create_query_string() {
		$array = array('key1'=>'value1','key2'=>'value2','key3'=>'');
		$this->assertMethodReturn('key1=value1&amp;key2=value2&amp;key3',$array);
	}
	
	public function test_get_scheme() {
		$this->assertMethodReturn('http','http://www.impact-crm.com/');
		$this->assertMethodReturn('ftp','ftp://www.impact-crm.com/help/');
		$this->assertMethodReturnFalse('mailto:me@simpo.org');
		$this->assertMethodReturn('file','file:///c:/Program Files/PHP/php.exe');
	}
	
	public function test_get_domain() {
		$this->assertMethodReturn(
			'www.impact-crm.com','http://www.impact-crm.com/'
		);
		$this->assertMethodReturn(
			'www.impact-crm.com','http://www.impact-crm.com'
		);
		$this->assertMethodReturn(
			'www.impact-crm.com','ftp://www.impact-crm.com/help/'
		);
		$this->assertMethodReturn(
			'www.impact-crm.com','ftp://test:test@www.impact-crm.com/help/'
		);
		
		$this->assertMethodReturnFalse('mailto:me@simpo.org');
	}
	
	public function test_get_username() {
		$this->assertMethodReturnFalse('http://www.impact-crm.com/');
		$this->assertMethodReturn(
			'test','http://test:pass@www.impact-crm.com/'
		);
		$this->assertMethodReturn(
			'test','http://test@www.impact-crm.com/'
		);
	}
	
	public function test_get_password() {
		$this->assertMethodReturnFalse('http://www.impact-crm.com/');
		$this->assertMethodReturnFalse('http://test@www.impact-crm.com/');
		$this->assertMethodReturn(
			'pass','http://test:pass@www.impact-crm.com/'
		);
	}
	
	public function test_remove_file_scheme() {
		// STUB
	}
	
	public function test_get_port() {
		$this->assertMethodReturn(8080,'http://www.impact-crm.com:8080/');
		$this->assertMethodReturn(8080,'http://www.impact-crm.com:8080');
		$this->assertMethodReturn(23,'ftp://www.impact-crm.com:23/help/');
		$this->assertMethodReturn(23,'ftp://test:test@www.impact-crm.com:23');
		$this->assertMethodReturn(23,'ftp://test@www.impact-crm.com:23');
	}
	
	public function test_get_computer() {
		$this->assertMethodReturn('computer','\\\\computer\\d$\\personal.doc');
		$this->assertMethodReturnFalse('\\\\');
		$this->assertMethodReturnFalse('http://www.impact-crm.com:8080/');
		$this->assertMethodReturnFalse('/usr/simpo/docs/personal.doc');
		$this->assertMethodReturn('computer','file:////computer/d$/personal.doc');
	}
	
	public function test_get_share() {
		$this->assertMethodReturn('d$','\\\\computer\\d$\\personal.doc');
		$this->assertMethodReturnFalse('\\\\computer\\');
		$this->assertMethodReturnFalse('http://www.impact-crm.com:8080/');
		$this->assertMethodReturnFalse('/usr/simpo/docs/personal.doc');
		$this->assertMethodReturn('d$','file:////computer/d$/personal.doc');
	}
	
	public function test_get_drive() {
		$this->assertMethodReturn('c','c:\\Program Files\\PHP\\php.exe');
		$this->assertMethodReturn('c','C:\\Program Files\\PHP\\php.exe');
		$this->assertMethodReturn('c','c:/Program Files/PHP/php.exe');
		$this->assertMethodReturnFalse('http://www.impact-crm.com/test');
		$this->assertMethodReturnFalse('/usr/simpo/docs/personal.doc');
		$this->assertMethodReturn('c','file:///c:/Program Files/PHP/php.exe');
	}
	
	public function test_get_path() {
		$this->assertMethodReturn('/','http://www.impact-crm.com/test');
		$this->assertMethodReturn('/','http://www.impact-crm.com/');
		$this->assertMethodReturn('/test/','http://www.impact-crm.com/test/help');
		$this->assertMethodReturn('/test/help/','http://www.impact-crm.com/test/help/');
		$this->assertMethodReturn('/test/help/','http://www.impact-crm.com/test/help/#TEST');
		$this->assertMethodReturn('/test/','http://www.impact-crm.com/test/help?id=7');
		$this->assertMethodReturn('/test/','http://www.impact-crm.com/test/help#test');
		$this->assertMethodReturn('/','http://www.impact-crm.com/#test');
		$this->assertMethodReturn('','index.htm');
		$this->assertMethodReturn('/','/index.htm');
		$this->assertMethodReturn('c:/Program Files/PHP/','c:\\Program Files\\PHP\\php.exe');
		$this->assertMethodReturn('/usr/simpo/docs/','/usr/simpo/docs/personal.doc');
		$this->assertMethodReturn('/usr/simpo/docs/personal/','/usr/simpo/docs/personal/');
		$this->assertMethodReturn('c:/Program Files/PHP/','file:///c:/Program Files/PHP/php.exe');
	}
	
	public function test_get_path_url() {
		// STUB
	}
	
	public function test_get_path_local() {
		// STUB
	}
	
	public function test_get_path_array() {
		$this->assertMethodReturn(array(),'http://www.impact-crm.com/test');
		$this->assertMethodReturn(array('test'),'http://www.impact-crm.com/test/');
		$this->assertMethodReturn(array('test'),'http://www.impact-crm.com/test/index.htm?id=5#test');
		$this->assertMethodReturn(array('usr','simpo','docs'),'/usr/simpo/docs/personal.doc');
	}
	
	public function test_count_leading_slashes() {
		$this->assertMethodReturn(1,'/usr/simpo/docs/personal.doc');
		$this->assertMethodReturn(0,'docs/personal.doc');
		$this->assertMethodReturn(2,'\\\\computer\\d$\\personal.doc');
	}
	
	public function test_count_trailing_slashes() {
		$this->assertMethodReturn(1,'/usr/simpo/docs/personal/');
		$this->assertMethodReturn(0,'/usr/simpo/docs/personal.doc');
		$this->assertMethodReturn(1,'\\\\share\\d$\\');
		$this->assertMethodReturn(0,'http://www.impact-crm.com/test/help#test');
		$this->assertMethodReturn(1,'http://www.impact-crm.com/test/help/#test');
		$this->assertMethodReturn(1,'http://www.impact-crm.com/test/help/?id=test');
	}
	
	public function test_get_filename() {
		$this->assertMethodReturn('test','http://www.impact-crm.com/test');
		$this->assertMethodReturnFalse('http://www.impact-crm.com/');
		$this->assertMethodReturn('help','http://www.impact-crm.com/test/help?id=7');
		$this->assertMethodReturn('help','http://www.impact-crm.com/test/help#test');
		$this->assertMethodReturn('index.htm','index.htm');
		$this->assertMethodReturn('index.htm','/index.htm');
		$this->assertMethodReturnFalse('http://www.impact-crm.com/test/help/');
		$this->assertMethodReturn('php.exe','file:///c:/Program Files/PHP/php.exe');
	}
	
	public function test_get_extension() {
		$this->assertMethodReturn('doc','/usr/simpo/docs/personal.doc');
		$this->assertMethodReturn('htm','index.htm');
		$this->assertMethodReturn('htm','/index.htm');
		$this->assertMethodReturn('php','http://www.impact-crm.com/test/help/index.php?id=1#test');
		$this->assertMethodReturn('htm','http://www.impact-crm.com/test/help/index.htm#test');
		$this->assertMethodReturn('doc','\\\\share\\d$\\help.doc');
		$this->assertMethodReturnFalse('/usr/simpo/docs/');
		$this->assertMethodReturnFalse('/usr/simpo/docs');
		$this->assertMethodReturn('tar.gz','/usr/simpo/docs/archive.tar.gz');
	}
	
	public function test_lchop_array() {
		$this->assertMethodReturn(
			array(3,4),
			array(array(1,2,3,4),2)
		);
	}
	
	public function test_chop_query_and_fragment() {
		$this->assertMethodReturn(
			'http://www.impact-crm.com/test/help',
			'http://www.impact-crm.com/test/help#test'
		);
		$this->assertMethodReturn(
			'http://www.impact-crm.com/test/help',
			'http://www.impact-crm.com/test/help?id=7'
		);
	}
	
	public function test_get_query() {
		$this->assertMethodReturn(
			array('id'=>'4'),'http://www.impact-crm.com/test?id=4'
		);
		$this->assertMethodReturn(
			array('id'=>'4','test'=>'7'),
			'http://www.impact-crm.com/test?id=4&test=7'
		);
		$this->assertMethodReturn(
			array('id'=>'4','test'=>'7'),
			'http://www.impact-crm.com/test?id=4&test=7#test'
		);
		$this->assertMethodReturn(
			array('open'=>''),
			'http://www.impact-crm.com/test?open#test'
		);
		$this->assertMethodReturnFalse('http://www.impact-crm.com/test');
	}
	
	public function test_parse_query() {
		$this->assertMethodReturn(array('id'=>'4'),'id=4');
		$this->assertMethodReturn(array('id'=>'4','test'=>'7'),'id=4&test=7');
		$this->assertMethodReturn(array('id'=>'4','test'=>'7'),'id=4&amp;test=7');
		$this->assertMethodReturn(array('open'=>''),'open');
	}
	
	public function test_get_fragment() {
		$this->assertMethodReturn(
			'test','http://www.impact-crm.com/test?id=4&test=7#test'
		);
		$this->assertMethodReturnFalse('http://www.impact-crm.com/test');
	}
	
	public function test_explode_path() {
		$this->assertMethodReturn(
			array('c:','Program Files','PHP','php.exe'),
			'c:\\Program Files\\PHP\\php.exe'
		);
		$this->assertMethodReturn(
			array('c:','Program Files','PHP','php.exe'),
			'c:/Program Files/PHP/php.exe'
		);
		$this->assertMethodReturn(
			array('usr','simpo','docs','personal.doc'),
			'/usr/simpo/docs/personal.doc'
		);
		
		$this->assertMethodReturn(
			array('c:','Program Files','PHP','php.exe'),
			array('c:\\Program Files\\PHP','php.exe')
		);
		$this->assertMethodReturn(
			array('c:','Program Files','PHP','php.exe'),
			array('c:\\Program Files\\','/PHP/php.exe')
		);
		
		$this->assertMethodReturn(
			array('http:','www.impact-crm.com','help','main?id=test#test'),
			'http://www.impact-crm.com/help/main?id=test#test'
		);
		$this->assertMethodReturn(
			array('http:','www.impact-crm.com','help','main?id=test#test'),
			array('http://www.impact-crm.com/','help/main?id=test#test')
		);
		$this->assertMethodReturn(
			array('http:','test:test@www.impact-crm.com','help','main?id=test#test'),
			'http://test:test@www.impact-crm.com/help/main?id=test#test'
		);
		
		$this->assertMethodReturn(
			array('file:','c:','Program Files','PHP','php.exe'),
			'file:///c:/Program Files/PHP/php.exe'
		);
    }
	
	public function test_fix_bad_slashes() {
		$this->assertMethodReturn(
			'c:/Program Files/PHP/php.exe',
			'c:\\Program Files\\PHP\\php.exe'
		);
		$this->assertMethodReturn(
			'//computer/c$/Program Files/PHP/php.exe',
			'\\\\computer\\c$\\Program Files\\PHP\\php.exe'
		);
	}
	
	public function test_fix_bad_path() {
		$this->assertMethodReturn(
			'http://www.impact-crm.com/help/?page=test%3Fid=4%3Fpart=6',
			'http://www.impact-crm.com/help/?page=test?id=4?part=6'
		);
		$this->assertMethodReturn(
			'http://www.impact-crm.com/help/main#test',
			'http://www.impact-crm.com/help/main#test'
		);
		$this->assertMethodReturn(
			'http://www.impact-crm.com/help/main#test%23id',
			'http://www.impact-crm.com/help/main#test#id'
		);
		$this->assertMethodReturn(
			'http://www.impact-crm.com/help/?page=test#test%23id',
			'http://www.impact-crm.com/help/?page=test#test#id'
		);
		$this->assertMethodReturn(
			'http://www.impact-crm.com/help/?page=test#test%3Fid=5',
			'http://www.impact-crm.com/help/?page=test#test?id=5'
		);
		$this->assertMethodReturn(
			'http://test:test@www.impact-crm.com/help/?page=test#test%3Fid=5',
			'http://test:test@www.impact-crm.com/help/?page=test#test?id=5'
		);
		$this->assertMethodReturn(
			'http://test:test@www.impact-crm.com/help/?page=test#test%3Fid=5%40test=3',
			'http://test:test@www.impact-crm.com/help/?page=test#test?id=5@test=3'
		);
	}
	
	public function test_fix_bad_query() {
		$this->assertMethodReturn(
			'http://www.impact-crm.com/help/?page=test',
			'http://www.impact-crm.com/help/?page=test'
		);
		$this->assertMethodReturn(
			'http://www.impact-crm.com/help/?page=test%3Fid=4',
			'http://www.impact-crm.com/help/?page=test?id=4'
		);
		$this->assertMethodReturn(
			'http://www.impact-crm.com/help/?page=test%3Fid=4%3Fpart=6',
			'http://www.impact-crm.com/help/?page=test?id=4?part=6'
		);
	}
	
	public function test_fix_bad_at() {
		$this->assertMethodReturn(
			'http://test:test@www.impact-crm.com/help/?page=test',
			'http://test:test@www.impact-crm.com/help/?page=test'
		);
		$this->assertMethodReturn(
			'mailto:me@simpo.org',
			'mailto:me@simpo.org'
		);
		$this->assertMethodReturn(
			'http://test:test@www.impact-crm.com/help/%40test',
			'http://test:test@www.impact-crm.com/help/@test'
		);
	}
	
	public function test_fix_bad_fragment() {
		$this->assertMethodReturn(
			'http://www.impact-crm.com/help/main#test',
			'http://www.impact-crm.com/help/main#test'
		);
		$this->assertMethodReturn(
			'http://www.impact-crm.com/help/main#test%23id',
			'http://www.impact-crm.com/help/main#test#id'
		);
		$this->assertMethodReturn(
			'http://www.impact-crm.com/help/main#test%23id%23part1',
			'http://www.impact-crm.com/help/main#test#id#part1'
		);
	}
	
	public function test_has_scheme() {
		$this->assertMethodReturnTrue('http://www.impact-crm.com');
		$this->assertMethodReturnTrue('http://www.impact-crm.com/help?id=test');
		$this->assertMethodReturnFalse('c:\\Program Files\\PHP\\php.exe');
		$this->assertMethodReturnFalse('/usr/simpo/docs/personal.doc');
		$this->assertMethodReturnTrue('file:///c:/Program Files/PHP/php.exe');
	}
	
	public function test_has_username() {
		$this->assertMethodReturnFalse('http://www.impact-crm.com');
		$this->assertMethodReturnTrue('http://test:test@www.impact-crm.com');
		$this->assertMethodReturnTrue('http://test@www.impact-crm.com');
		$this->assertMethodReturnFalse('http://www.impact-crm.com:80');
		$this->assertMethodReturnFalse('http://www.impact-crm.com:80/help?id=test');
		$this->assertMethodReturnTrue('http://test@www.impact-crm.com:80/help?id=test');
		$this->assertMethodReturnTrue('http://test:test@www.impact-crm.com:80/help?id=test');
		$this->assertMethodReturnFalse('http://www.impact-crm.com');
		$this->assertMethodReturnFalse('file:///c:/Program Files/PHP/php.exe');
	}
	
	public function test_has_password() {
		$this->assertMethodReturnFalse('http://www.impact-crm.com');
		$this->assertMethodReturnTrue('http://test:test@www.impact-crm.com');
		$this->assertMethodReturnFalse('http://test@www.impact-crm.com');
		$this->assertMethodReturnFalse('http://www.impact-crm.com:80');
		$this->assertMethodReturnFalse('http://www.impact-crm.com:80/help?id=test');
		$this->assertMethodReturnFalse('http://test@www.impact-crm.com:80/help?id=test');
		$this->assertMethodReturnTrue('http://test:test@www.impact-crm.com:80/help?id=test');
		$this->assertMethodReturnFalse('file:///c:/Program Files/PHP/php.exe');
	}
	
	public function test_has_port() {
		$this->assertMethodReturnFalse('http://www.impact-crm.com');
		$this->assertMethodReturnTrue('http://test:test@www.impact-crm.com:80/help?id=test');
		$this->assertMethodReturnFalse('http://test:test@www.impact-crm.com/help?id=test:80');
		$this->assertMethodReturnFalse('http://test@www.impact-crm.com');
		$this->assertMethodReturnFalse('file:///c:/Program Files/PHP/php.exe');
	}
	
}
?>