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
	
	private function _init($path='',$filename='') {
		if (is_string($path)) {
			if ($path != '') {
				if ($filename == '') {
					$this->fullpath = realpath($path);
					$this->path = $path;
					$this->_set_filename();
					$this->_set_ext();
				} else {
					$this->fullpath = realpath($path.$filename);
					$this->path = $path;
					$this->filename = $filename;
					$this->_set_ext();
				}
			}
		} else {
			$this->fullpath = realpath($path->path.$filename);
			$this->path = $path->path;
			$this->filename = $filename;
			$this->_set_ext();
		}
	}
	
	private function _translate_method($method) {
		$method = strtolower(trim($method));
		if (array_key_exists($method,$this->methods)) {
			return $this->methods[$method];
		} else {
			throw new Exception('Unknown open method: "'.$method.'"');
		}
	}
	
	private function _set_filename() {
		$pattern = '/.*'.chr(92).DS.'([^'.chr(92).DS.']+)/';
		preg_match($pattern,$this->fullpath,$match);
		if (!empty($match)) {
			$this->filename = $match[1];
		}
	}
	
	private function _set_ext() {
		$pattern = '/.*'.chr(92).DS.'[^'.chr(92).DS.']*?\.([^'.chr(92).DS.']+)/';
		preg_match($pattern,$this->fullpath,$match);
		if (!empty($match)) {
			$this->ext = $match[1];
		}
	}
	
	public function set_file($path='',$filename='') {
		$this->_init($path,$filename);
	}
	
	public function open($method='read',$opentype='') {
		$method = $this->_translate_method($method);
		if ($opentype == 'settings') {
			$this->_load_xml_parameters();
		} else {
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
	}
	
	/**
     *  Get a line from the log file.
     *
     *  @public
     *  @return string The line from the filehandle.
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
     *  Load the parameters from a specified XML file.
     *
     *  @private
     */
    private function _load_xml_parameters() {
        if (is_file($this->fullpath)) {
			$config = simplexml_load_file($this->fullpath);
			$this->fh = $config->param;
			$this->handleType = 'simplexml';
        } else {
            throw new Exception('Could not open file: "'.$this->fullpath.'".');
        }
    }
}
?>