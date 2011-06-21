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
class File extends Base {
	private $settings = array();
	private $fh;
	private $methods = array(
		'read' => 'r', 'append' => 'a', 'write' => 'wt',
		'readwrite' => 'rwt'
	);

	public function __construct($path='',$filename='') {
		$this->_init($path,$filename);
	}
	
	/**
	 *	Generic get property method.
	 *
	 *	Get the value of an application property.  Values are stored in
	 *	the application array and accessed via the __set and __get methods.
	 *
	 *	@public
	 */
	public function __get($property) {
		$convertedProperty = I::camelize($property);
		if (isset($this->settings[$convertedProperty])) {
			return $this->settings[$convertedProperty];
		} else {
			if ($property == 'settings') {
				return $this->settings;
			}
			throw new Exception('Property: '.$convertedProperty.', does not exist');
		}
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
				$this->fh = @fopen($this->fullpath,$method);
				if (!$this->fh) {
					throw new Exception('Could not open file: "'.$this->fullpath.'".');
				}
				$this->handleType = 'filehandle';
			} else {
				throw new Exception('Filename: "'.$this->fullpath.'", is not valid.');
			}	
		}
	}
	
	public function close() {
		if ($this->_is_resource()) {
			fclose($this->fh);
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
			if ($this->fh) {
				if (!feof($this->fh)) {
					return fgets($this->fh);
				}
			}
		
			return null;
		}
    }
	
	public function all() {
		if ($this->_is_resource()) {
			$contents = '';
		
			rewind($this->fh);
			do {
				$cLine = $this->next();
				$contents .= $cLine;
			} while ($cLine != null);
		
			return $contents;
		}
	}
	
	private function _is_resource() {
		$handleType = gettype($this->fh);
		return ($this->_is_equal($handleType,'resource'));
	}
	
	private function _is_equal($string1,$string2) {
		return (strtolower(trim($string1)) == strtolower(trim($string2)));
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
	
	public function __destruct() {
		$this->close();
	}
}
?>