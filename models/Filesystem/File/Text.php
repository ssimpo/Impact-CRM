<?php
defined('DIRECT_ACCESS_CHECK') or die;

/*
 *	Plain-text file handling.
 *	
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.7
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Filesystem
 */
class Filesystem_File_Text extends Filesystem implements Filesystem_File_Object {
	private $cache = array();
	private $cacheSize = 2500;
	private $cachePosition = -1;
	
	/**
	 *	Open the specified file, using the given method and store filehandle.
	 *
	 *	@public
	 *	@param string $fullpath The full path (inc. filename) to the text file.
	 *	@param string $method The open method to use (r|wt|a|rwt).
	 */
	public function load($fullpath,$method) {
		$this->handle = @fopen($fullpath,$method);
		if (!$this->handle) {
			throw new Exception('Could not open file: "'.$fullpath.'".');
		}
	}
	
	/**
     *  Get a line from the open file.
     *
     *  @public
     *  @return string The line from the open filehandle.
     */
    public function next() {
		if (($this->cachePosition == -1) || ($this->cachePosition >= count($this->cache))) {
			$this->_load_cache();
			if ($this->cachePosition == -1) {
				return null;
			}
		}
		
		$line = $this->cache[$this->cachePosition];
		$this->cachePosition++;
		return $line;
    }
	
	private function _get_next_line() {
		if ($this->_is_resource()) {
			if ($this->handle) {
				if (!feof($this->handle)) {
					$line = fgets($this->handle);
					return $line;
				}
			}
		}
	}
	
	private function _load_cache() {
		$this->cache = array();
		$this->cachePosition = -1;
		
		for ($i = 0; $i < $this->cacheSize; $i++) {
			$line = $this->_get_next_line();
			if (!is_null($line)) {
				$this->cache[$i] = $line;
			} else {
				break;
			}
		}
		
		$this->cachePosition = 0;
	}
	
	public function write($data) {
		if ($this->_is_resource()) {
			if ($this->handle) {
				fwrite($this->handle,$data);
			}
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
		
		return null;
	}
	
	/**
	 *	Close the current file handle.
	 *
	 *	The is method is called automatically by the class destructor.
	 */
	public function close() {
		if ($this->_is_resource()) {
			print "closing\n";
			fclose($this->handle);
		}
	}
}
?>