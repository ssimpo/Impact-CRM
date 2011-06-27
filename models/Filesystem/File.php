<?php
defined('DIRECT_ACCESS_CHECK') or die;

/*
 *	File handling class
 *
 *	File Open/Close and parsing operations.  Will open files, navigate through
 *	them and parse them according to installed sub-classes.
 *	
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.6
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Filesystem
 */
class Filesystem_File extends Filesystem implements ArrayAccess,Countable,Iterator {
	private $parser;
	private $methods = array(
		'read' => 'r', 'append' => 'a', 'write' => 'wt',
		'readwrite' => 'rwt'
	);
	public function __construct($path='',$filename='') {
		$this->_init($path,$filename);
	}
	
	public function __call($name,$arguments) {
		if (array_key_exists($name,$this->methods)) {
			return call_user_func_array(array($this->parser,$name),$arguments);
		} else {
			throw new Exception('Undefined method "'.$name.'"');
		}
	}
	
	public function offsetExists($offset) {
		if (array_key_exists('offsetExists',$this->methods)) {
			return call_user_func(array($this->parser,'offsetExists'),$offset);
		} else {
			throw new Exception('This is not an object of type array');
		}
	}
	public function offsetGet($offset) {
		if (array_key_exists('offsetGet',$this->methods)) {
			return call_user_func(array($this->parser,'offsetGet'),$offset);
		} else {
			throw new Exception('This is not an object of type array');
		}
	}
	public function offsetSet($offset,$value) {
		if (array_key_exists('offsetSet',$this->methods)) {
			return call_user_func(array($this->parser,'offsetSet'),$offset,$value);
		} else {
			throw new Exception('This is not an object of type array');
		}
	}
	public function offsetUnset($offset) {
		if (array_key_exists('offsetUnset',$this->methods)) {
			return call_user_func(array($this->parser,'offsetUnset'),$offset);
		} else {
			throw new Exception('This is not an object of type array');
		}
	}
	public function count() {
		if (array_key_exists('count',$this->methods)) {
			return call_user_func(array($this->parser,'count'));
		} else {
			throw new Exception('This is not an object of type array');
		}
	}
	public function current() {
		if (array_key_exists('current',$this->methods)) {
			return call_user_func(array($this->parser,'current'));
		} else {
			throw new Exception('This is not an object of type array');
		}
	}
	public function key() {
		if (array_key_exists('key',$this->methods)) {
			return call_user_func(array($this->parser,'key'));
		} else {
			throw new Exception('This is not an object of type array');
		}
	}
	public function next() {
		if (array_key_exists('next',$this->methods)) {
			return call_user_func(array($this->parser,'next'));
		} else {
			throw new Exception('This is not an object of type array');
		}
	}
	public function rewind() {
		if (array_key_exists('rewind',$this->methods)) {
			return call_user_func(array($this->parser,'rewind'));
		} else {
			throw new Exception('This is not an object of type array');
		}
	}
	public function valid() {
		if (array_key_exists('valid',$this->methods)) {
			return call_user_func(array($this->parser,'valid'));
		} else {
			throw new Exception('This is not an object of type array');
		}
	}
	
	
	/**
	 *	Set the internal properties for filename and paths ...etc.
	 *
	 *	@private
	 *	@param string $path The file-path.
	 *	@param string $path The filename.
	 */
	private function _init($path='',$filename='') {
		if (is_string($path)) {
			if ($path != '') {
				if ($filename == '') {
					$this->fullpath = realpath($path);
					$this->path = realpath($path);
					$this->filename = $this->_get_filename($this->fullpath);
					$this->ext = $this->_get_ext($this->fullpath);
				} else {
					if (substr($path,-1) != DS) {
						$this->fullpath = realpath($path.DS.$filename);
					} else {
						$this->fullpath = realpath($path.$filename);
					}
					$this->path = realpath($path);
					$this->filename = $filename;
					$this->ext = $this->_get_ext($this->fullpath);
				}
			}
		} else {
			$this->fullpath = realpath($path->path.$filename);
			$this->path = $path->path;
			$this->filename = $filename;
			$this->ext = $this->_get_ext($this->fullpath);
		}
	}
	
	/**
	 *	Translate a method name into it's PHP equivalent.
	 *
	 *	@private
	 *	@param string $method The method name.
	 *	@return string
	 */
	private function _translate_method($method) {
		$method = strtolower(trim($method));
		if (array_key_exists($method,$this->methods)) {
			return $this->methods[$method];
		} else {
			throw new Exception('Unknown open method: "'.$method.'"');
		}
	}
	
	/**
	 *	Get the filename from the full-path.
	 *
	 *	@private
	 *	@param string $fullpath The fullpath to the file.
	 *	@return string The filename.
	 */
	private function _get_filename($fullpath) {
		$pattern = '/.*'.self::BSLASH.DS.'([^'.self::BSLASH.DS.']+)/';
		preg_match($pattern,$fullpath,$match);
		if (!empty($match)) {
			return $match[1];
		}
		
		return false;
	}
	
	/**
	 *	Get the file-extension from the full-path.
	 *
	 *	@private
	 *	@param string $fullpath The fullpath to the file.
	 *	@return string The file-extension.
	 */
	private function _get_ext($fullpath) {
		$pattern = '/.*'.self::BSLASH.DS.'[^'.self::BSLASH.DS.']*?\.([^'.self::BSLASH.DS.']+)/';
		preg_match($pattern,$fullpath,$match);
		if (!empty($match)) {
			return $match[1];
		}
		
		return false;
	}
	
	/**
	 *	Set the current path and file.
	 *
	 *	@public
	 *	@param string $path The path to the file.
	 *	@param string filename The filename of the file.
	 *	
	 */
	public function set_file($path='',$filename='') {
		$this->_init($path,$filename);
	}
	
	/**
	 *	Open the currently set file.
	 *
	 *	Will open a file in the specified mode and then parse it according
	 *	to the supplied type.
	 *
	 *	@public
	 *	@param string $method The open method to use (read|write|append|readwrite).
	 *	@param string $parseType How to parse this file.
	 */
	public function open($method='read',$parserType='text') {
		$method = $this->_translate_method($method);
		if (is_file($this->fullpath)) {
			$this->parser = $this->_load_parser($parserType,$method);
			$this->methods = array_flip(get_class_methods($this->parser));
		} else {
			throw new Exception('Filename: "'.$this->fullpath.'", is not valid.');
		}	
	}
	
	/**
	 *	Load the file-parser.
	 *
	 *	@private
	 *	@param string $parserType The name of the parser to load.
	 *	@param string $method The open method to use (r|wt|a|rwt).
	 *	@return object The loaded parser.
	 */
	private function _load_parser($parserType,$method) {
		$parser = $this->factory('Filesystem_File_'.$parserType);
		$parser->load($this->fullpath,$method);
		return $parser;
	}
	
	public function close() {
		
	}
}
?>