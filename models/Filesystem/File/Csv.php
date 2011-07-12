<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *  Lotus/IBM Domino CSV class
 *  
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.3
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Filesystem
 */
class Filesystem_File_Csv extends  Filesystem_File_Text {
    private $lastLine = null;
	private $position = -1;
    private $headers = array();
    private $filters = array();
    private $parseAs = array();
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
            rewind($this->handle);
            $this->position = -1;
            $this->headers = array();
            
            $line = parent::next();
            if (!is_null($line)) {
                $this->headers = $this->parse($line);
            }
        }
        
        $this->position = 0;
    }
    
    public function set_data_type($field,$type) {
        $this->parseAs[$field] = $type;
    }
    
    public function set_header($index,$name) {
        $this->headers[$index] = $name;
    }
    
    /**
     *  @public
     *  @param string $line One line from the log.
     *  @return array() The results of the parsing.
     */
    public function parse($line) {
        $array = preg_split('/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/',$line);
        $array = $this->_parse_columns($array);
        if ($this->position != -1) {
            $array = $this->_use_column_headers($array);
        }
        
        $newArray = array();
        foreach ($array as $key => $value) {
            $newArray[$key] = $this->_get_correct_type($value,$key);
        }
    
        return $newArray;
    }
    
    private function _use_column_headers($array) {
        $newArray = array();
        for ($i = 0; $i < count($array); $i++) {
            if (isset($this->headers[$i])) {
                $newArray[$this->headers[$i]] = $array[$i];
            } else {
                $newArray[$i] = $array[$i];
            }
        }
    
        return $newArray;
    }
    
    private function _get_correct_type($value,$index) {
        if (isset($this->headers[$index])) {
            $index = $this->headers[$index];
        }
       
        $type = 'text';
        if (isset($this->parseAs[$index])) {
            $type = $this->parseAs[$index];
        }
        
        switch (strtolower($type)) {
            case 'int': case 'integer': return $this->_get_int_value($value);
            case 'float': case 'double': case 'real': return (float) $value;
            case 'date': return $this->_get_date_value($value);
            case 'boolean': return $this->_get_boolean_value($value);
            default: return $value;
        }
    }
    
    /**
     *  Return the supplied value as an integer.
     *  
     *  @private
     *  @param string $value The value to use
     *  @return int
     */
    private function _get_int_value($value) {
        $value = trim($value);
        
        if (!is_numeric($value)) {
            return 0;
        } else {
            return (int) $value;
        }
    }
    
    /**
     *  Return the supplied value as an a boolean.
     *
     *  Interpret text-values that indicate true or false.  (Eg. Yes|No,
     *  On|Off, true|false).
     *  
     *  @private
     *  @param string $value The value to use
     *  @return boolean
     */
    private function _get_boolean_value($value) {
        switch (strtolower($value)) {
			case 'true': case 'yes': case 'on':
				return true;
			case 'false': case 'no': case 'off':
				return false;
		}
        
        return false;
    }
    
    /**
     *  Return the a Calendar_DateTime object  for the specified value.
     *  
     *  @private
     *  @param string $value The value to use
     *  @return Calendar_DateTime
     */
    private function _get_date_value($value) {
        if (!is_int($value)) {
            $dateparser = new DateParser();
			return $dateparser->parse($value);
        }
        $date = new Calendar_DateTime();
        $date->epoc = $value;
        return $date;
    }
    
    private function _parse_columns($columns) {
        for ($i = 0; $i < count($columns); $i++) {
            $columns[$i] = preg_replace('/\A"|"[\n\r\f]+\Z|"\Z/','',$columns[$i]);
            $columns[$i] = str_replace('""','"',$columns[$i]);
        }
        
        return $columns;
    }
    
    public function write($data,$filter='') {
        if ($filter != '') {
            if ($this->_is_numeric_indexed_array($data)) {
                $data = $this->_use_column_headers($data);
            }
            if (!isset($this->filters[$filter])) {
                $this->filters[$filter] = new Filesystem_Filter($filter);
            }
            $filter = $this->filters[$filter];
            if (!$filter->include_line($data)) {
                return false;
            }
        }
        parent::write($this->rebuild_line($data));
    }
    
    public function rebuild_line($row) {
        $line = '';
        foreach ($row as $colValue) {
            $colValue = str_replace('"','""',$colValue);
            $line .= '"'.$colValue.'"';
        }
        return $line."\n";
    }
    
    /**
	 *
	 *	Test if an array is indexed numerically.
	 *	
	 *	@private
	 *	@param Array() $array The array to test.
	 *	@return	Boolean
	 */
	private function _is_numeric_indexed_array($array) {
		$is_numeric_indexed = true;
		
		foreach ($array as $key => $value) {
			if (!is_numeric($key)) {
				$is_numeric_indexed = false;
			}
		}
		
		return $is_numeric_indexed;
	}
    
    
}
?>