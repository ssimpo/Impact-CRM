<?php
defined('DIRECT_ACCESS_CHECK') or die;

/*
 *	File handling class
 *
 *	File Open/Close and parsing operations.  Will open files, navigate through
 *	them and parse them according to installed sub-classes.
 *	
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Filesystem
 */
class Filesystem_File extends Filesystem_ArrayAccess {
	protected $parser;
	protected $pathParser;
	private $methods = array(
		'read' => 'r', 'append' => 'a', 'write' => 'wt',
		'readwrite' => 'rwt'
	);
	
	public function __construct($path='',$filename='') {
		$this->_init($path,$filename);
	}
	
	public function __get($property) {
		$convertedProperty = I::camelize($property);
		switch($convertedProperty) {
			case 'path': return $this->pathParser->path;
			case 'filename': return $this->pathParser->filename;
			case 'ext': return $this->pathParser->extension;
			case 'fullpath': case 'fullPath': return $this->pathParser->fullpath;
			case 'fileSize':
				return filesize($this->fullpath);
			case 'accessed':
				$date = new Calendar_DateTime();
				$date->epoc = fileatime($this->fullpath);
				return $date;
			case 'modified':
				$date = new Calendar_DateTime();
				$date->epoc = filemtime($this->fullpath);
				return $date;
			default:
				if (property_exists($this->parser,$property)) {
					return $this->parser->$property;
				}
				return parent::__get($property);
		}
	}
	
	public function __set($property,$value) {
		$convertedProperty = I::camelize($property);
		switch($convertedProperty) {
			default:
				if (property_exists($this->parser,$property)) {
					$this->parser->$property = $value;
				} else {
					throw new Exception('Property "'.$property.'", does not exist.');
				}
				
		}
	}
	
	/**
	 *	Call a method within the file parser.
	 *
	 *	This allows public methods within the parser to be reflected into this
	 *	object instance.  Using this methodology, the parser is never accessed
	 *	directly but through the file object, which reflects a range of methods
	 *	depending on what is being parsed.
	 *
	 *	@public
	 *	@param string $name The name of the parser method to call.
	 *	@param array() $arguments The parameters to send to the parser method.
	 *	@return mixed The results of executing the method.
	 */
	public function __call($name,$arguments) {
		if (array_key_exists($name,$this->methods)) {
			return $this->_call_user_func_array($this->parser,$name,$arguments);
		} else {
			throw new Exception('Undefined method "'.$name.'"');
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
		$this->pathParser = new Filesystem_Path($path,$filename);
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
		if ((is_file($this->fullpath)) || ($method != 'r')) {
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