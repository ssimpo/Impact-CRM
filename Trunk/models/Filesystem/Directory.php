<?php
defined('DIRECT_ACCESS_CHECK') or die;

/*
 *	Directory handling class
 *
 *	Directory Open/Close, browsing and parsing operations.
 *	
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.6
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Filesystem
 *
 *	@todo Permission for user on files/directories?
 */
class Filesystem_Directory extends Filesystem {
	
	public function __construct($path='',$filter='') {
		$this->_init($path,$filter);
	}
	
	private function _init($path,$filter) {
		if ($path != '') {
			$newpath = str_replace(
				array(self::FSLASH,self::BSLASH),array(DS,DS),
				$path
			);
			
			$this->path = realpath($newpath);
		} else {
			$this->path = realpath(getcwd());
		}
		$this->_set_filter($filter);
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
	 *	Method assumes inteligence on the beahlf of the user as only minor
	 *	checking is applied.  Anything, which contains a forward-slash at the
	 *	stary is assumed to be already formatted as a regx and is simply
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
	public function set_directory($path='',$filter='') {
		$this->_init($path,$filter);
		$this->_open();
	}
	
	/**
     *  Move the directory pointer to the start.
     *
     *  @public
     *  @return string The current filename.
     */
    public function reset() {
        rewinddir($this->handle);
    }
	
	/**
     *  Move the directory pointer forward and return the next entry.
     *
     *  Uses the set filter in filtering-out unwanted entries.
     *
     *  @public
     *  @param boolean $getFileObject If set return a Filesystem_File object instead of just the filename.
     *  @return string|Filesystem_File The current filename or Filesystem_File object of that file.
     */
    public function next($getFileObject = false) {
		
		while (($filename = readdir($this->handle)) !== false) {
			if ($this->_filter($filename)) {
				if ($getFileObject === true) {
					return new Filesystem_File($this->path,$filename);
				} else {
					return $filename;	
				}
			}
		}
		
		return false;
    }
	
	/**
     *  Open a directory according to object settings.
     *
     *  @private
     */
    private function _open() {
        if (is_dir($this->path)) {
			if ($this->_is_resource()) {
				// If there is already a resource open, close it.
				$this->close();
			}
			
            if (!($this->handle = opendir($this->path))) {
                throw new Exception('Could not access : '.$this->path);
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