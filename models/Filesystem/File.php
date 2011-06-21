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
class Filesystem_File extends Filesystem {
	private $methods = array(
		'read' => 'r', 'append' => 'a', 'write' => 'wt',
		'readwrite' => 'rwt'
	);

	public function __construct($path='',$filename='') {
		$this->_init($path,$filename);
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
					$this->path = $path;
					$this->filename = $this->_get_filename($this->fullpath);
					$this->ext = $this->_get_ext($this->fullpath);
				} else {
					if (substr($path,-1) != DS) {
						$this->fullpath = realpath($path.DS.$filename);
					} else {
						$this->fullpath = realpath($path.$filename);
					}
					$this->path = $path;
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
	 *	@todo Add parsing features.
	 *
	 *	@public
	 *	@param string $method The open method to use (read|write|append|readwrite).
	 *	@param string $parseType How to parse this file.
	 */
	public function open($method='read',$opentype='standard') {
		$method = $this->_translate_method($method);
		
		if (is_file($this->fullpath)) {
			$this->handle = @fopen($this->fullpath,$method);
			if (!$this->handle) {
				throw new Exception('Could not open file: "'.$this->fullpath.'".');
			}
			$this->handleType = 'filehandle';
		} else {
			throw new Exception('Filename: "'.$this->fullpath.'", is not valid.');
		}	
	}
	
	/**
     *  Get a line from the open file.
     *
     *  @public
     *  @return string The line from the open filehandle.
     */
    public function next() {
		if ($this->_is_resource()) {
			if ($this->handle) {
				if (!feof($this->handle)) {
					return fgets($this->handle);
				}
			}
		
			return null;
		}
    }
	
	/**
     *  Get a the entire file contents.
     *
     *  @public
     *  @return string The contents from the open filehandle.
     */
	public function all() {
		if ($this->_is_resource()) {
			$contents = '';
		
			rewind($this->handle);
			do {
				$cLine = $this->next();
				$contents .= $cLine;
			} while ($cLine != null);
		
			return $contents;
		}
	}
	
	/**
	 *	Close the current file handle.
	 *
	 *	The is method is called automatically by the class destructor.
	 */
	public function close() {
		if ($this->_is_resource()) {
			fclose($this->handle);
		}
	}
}
?>