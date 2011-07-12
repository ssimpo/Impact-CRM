<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *  Lotus/IBM Domino CSV class
 *  
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Filesystem
 */
class Filesystem_File_Csv extends  Filesystem_File_Text {
    private $lastLine = null;
	private $position = -1;
    private $headers = array();
    public $firstLineHeaders = false;
    
    /**
     *  Constructor
     *
     *  Load the Domino log-format options.
     */
    public function __construct() {
    }
    
    /**
     *  Get a line from the open file and parse it.
     *
     *  @public
     *  @return array() The line from the open filehandle, parsed according to class rules.
     */
	public function next() {
        if ($this->position == -1) {
            $this->_get_headers();
        }
        
        $line = parent::next();
		if (!is_null($line)) {
			$this->position++;
			$this->lastLine = $this->parse($line);
			return $this->lastLine;
		}
	}

	public function rewind() {
        $this->position = -1;
		rewind($this->handle);
		$this->next();
	}
	
	public function current() {
        return $this->lastLine;
    }
	
	public function valid() {
        if (feof($this->handle)) {
			return false;
		} else {
			return true;
		}
    }
	
	public function key() {
        return $this->position;
	}
	
	public function all() {
	}
    
    /**
     *  Get the first line and store as the column headers.
     *
     *  @private
     */
    private function _get_headers() {
        if ($this->firstLineHeaders) {
            $line = parent::next();
            if (!is_null($line)) {
                $this->headers = $this->parse($line);
            }
        }
        $this->position = 0;
    }
    
    /**
     *  @public
     *  @param string $line One line from the log.
     *  @return array() The results of the parsing.
     */
    public function parse($line) {
        $array = preg_split('/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/',$line);
        $array = $this->_parse_columns($array);
        if (($this->firstLineHeaders) && ($this->position != -1)) {
            $newArray = array();
            for ($i = 0; (($i < count($this->headers)) && ($i < count($array))); $i++) {
                $newArray[$this->headers[$i]] = $array[$i];
            }
            return $newArray;
        } else {
            return $array;
        }
    }
    
    private function _parse_columns($columns) {
        for ($i = 0; $i < count($columns); $i++) {
            $columns[$i] = preg_replace('/\A"|"[\n\r\f]+\Z|"\Z/','',$columns[$i]);
        }
        
        return $columns;
    }
    
    public function write($data,$filter='') {
    }
}
?>