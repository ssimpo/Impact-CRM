<?php
defined('DIRECT_ACCESS_CHECK') or die;

/*
 *	Directory handling class
 *
 *	Directory Open/Close, browsing and parsing operations.
 *	
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.8
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Filesystem
 *
 *	@todo Permission for user on files/directories?
 */
class Filesystem_Directory extends Filesystem implements Iterator {
	private $dirList = false;
	private $pathParser = '';
	private $position = 0;
	private $lastFile = false;
	
	public function __construct($path='',$filter='',$fileMethod='read',$fileType='text') {
		$this->getFileObject = false;
		$this->_init($path,$filter,$fileMethod,$fileType);
	}
	
	public function __get($property) {
		$convertedProperty = I::camelize($property);
		switch($convertedProperty) {
			case 'path': return $this->pathParser->path;
			default:
				return parent::__get($property);
		}
	}
	
	private function _init($path,$filter,$fileMethod,$fileType) {
		if ($path == '') {
			$path = realpath(getcwd());
		} else {
			$path = $this->_add_slash($path);
		}
		
		$this->pathParser = new Filesystem_Path($path);
		$this->_set_filter($filter);
		$this->fileMethod = $fileMethod;
		$this->fileType = $fileType;
		$this->dirList = false;
		$this->position = 0;
	}
	
	
	/**
	 *	Add a slash to the end of the path if one is not present.
	 *
	 *	A directory path, should end in a slash but often it is ommitted, this
	 *	method will add a slash if one is not present.
	 *
	 *	@private
	 *	@param string $path The path to add a slash to.
	 *	@return string
	 */
	private function _add_slash($path) {
		$lastChar = substr($path,-1);
		if (($lastChar != self::BSLASH) && ($lastChar != self::FSLASH)) {
			$path = $path.self::FSLASH;
		}
		return $path;
	}
	
	/**
	 *	Set a filter on the current directory output
	 *
	 *	@private
	 *	@param string $filter The filter to apply.
	 */
	private function _set_filter($filter) {
		if (trim($filter) != '') {
			$this->filter = $this->_parse_filter($filter);
		} else {
			$this->filter = '';
		}
	}
	
	/**
	 *	Parse a supplied filter into a regx.
	 *
	 *	Parse the filter into a format that can be used via preg_match.
	 *	Method assumes intelligence on the behalf of the user as only minor
	 *	checking is applied.  Anything, which contains a forward-slash at the
	 *	start is assumed to be already formatted as a regx and is simply
	 *	returned.  Strings formatted such as '*.log' would be converted to
	 *	'/(?:.*\.log)/i' and '*.gif,*.png,*.jpg' would be reformatted and
	 *	returned as '/(?:.*\.gif|.*\.gif|.*\.jpg)/i'.
	 *
	 *	@private
	 *	@param string $filter The filter to parse.
	 *	@return string Regx search-pattern to use as a filter.
	 */
	private function _parse_filter($filter) {
		if (trim($filter) == '') {
			return '';
		}
		
		if (substr($filter,0,1) == self::FSLASH) {
			return $filter;
		} else {
			$newfilter = str_replace(
				array('*.',','), array('.*'.self::BSLASH.'.','|'),
				$filter
			);
			return self::FSLASH.'(?:'.$newfilter.')'.self::FSLASH.'i';
		}
	}
	
	/**
	 *	Set the current directory and filter.
	 *
	 *	@public
	 *	@param string $path The path to the directory to use.
	 *	@param string $filter The filter to use on the directory.
	 *	
	 */
	public function set_directory($path='',$filter='',$fileMethod='read',$fileType='text') {
		unset($this->dirList);
		$this->_init($path,$filter,$fileMethod,$fileType);
		$this->_open();
	}
	
	/**
     *  Move the directory pointer to the start.
     *
     *  @public
     *  @return string The current filename.
     */
    public function reset() {
        $this->rewind();
    }
	
	/**
     *  Move the position of the current array pointer, return the current array item and store it.
     *
     *  @note Can be run directly or via Array Iterator functionality.
     *
     *  @public
     *  @return string|Filesystem_File The current filename or Filesystem_File object of that file.
     */
    public function next() {
		if (empty($this->dirList)) {
			$this->_open();
		}
		
		if (!$this->valid()) {
			return false;
		}
		
		$filename = $this->dirList[$this->position];
		if ($this->getFileObject === true) {
			$file = new Filesystem_File($this->path,$filename);
			$file->open($this->fileMethod,$this->fileType);
			$this->lastFile = $file;
		} else {
			$this->lastFile = $filename;
		}
				
		$this->position++;
		return $this->lastFile;
    }
	
	/**
	 *	Array Iterator method, to reset the array to the start.
	 *
	 *	@public
	 */
	public function rewind() {
		$this->position = 0;
		$this->lastFile = false;
		if (empty($this->dirList)) {
			$this->_open();
		}
	}
	
	/**
	 *	Array Iterator method, to get the current file.
	 *
	 *	@note Relies on next() to have been run and file stored in $this->lastFile.
	 *
	 *	@public
	 */
	public function current() {
		if ($this->lastFile === false) {
			$this->next();
		}
		return $this->lastFile;
    }
	
	/**
	 *	Array Iterator method, to test that the next file is available.
	 *
	 *	@public
	 */
	public function valid() {
		$count = 0;
		if ($this->dirList) {
			$count = count($this->dirList);	
		}
		
		if ($count == 0) {
			return false;
		}
		
		if ($this->position < $count) {
			return true;
		} else {
			return false;
		}
    }
	
	/**
	 *	Array Iterator method, to get the current key name/number.
	 *	
	 *	@public
	 */
	public function key() {
		return $this->position;
	}
	
	/**
     *  Grab the contents of the directory, filter it and store in an array.
     *
     *  @private
     */
    private function _open() {
        if (is_dir($this->path)) {
			$dirList = scandir($this->path);
			if ($dirList === false) {
				throw new Exception('Could not read the directory : "'.$this->path.'."');
			}
			
			$this->dirList = array();
			for ($i=0; $i < count($dirList); $i++) {
				if ($this->_filter($dirList[$i])) {
					array_push($this->dirList,$dirList[$i]);
				}
			}
        } else {
            throw new Exception('Directory: '.$this->path.' does not exist');
        }
    }
	
	/**
	 *	Test whether the supplied string matches a regx filter.
	 *
	 *	@private
	 *	@param string $string The string to test.
	 *	@param string $filter The filter to use (if not supplied or blank the object, 'filter' property is used).
	 *	@return boolean
	 */
	private function _filter($string,$filter='') {
		if (($this->filter == '') && ($filter == '')) {
			return true;
		}
		if ($filter == '') {
			$filter = $this->filter;
		}
		
		$match = preg_match($filter,$string);
		if ($match !== false) {
			if ($match > 0) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 *	Close the current directory handle.
	 *
	 *	The is method is called automatically by the class destructor.
	 */
	public function close() {
		if ($this->_is_resource()) {
			closedir($this->handle);
		}
	}
	
}
?>