<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *  CSV handling class
 *  
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.6
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Filesystem
 */
class Filesystem_File_Csv extends Filesystem_File_Text {
    private $lastLine = null;
	private $position = -1;
    private $headers = array();
    private $filters = array();
    private $parseAs = array();
    public $firstLineHeaders = false;
    public $comma = ',';
    public $quotes = '"';
    
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
            $this->parseAs = array();
            
            $line = parent::next();
            if (!is_null($line)) {
                $this->headers = $this->parse($line);
                $this->_init_headers(count($this->headers));
            }
        }
        
        $this->position = 0;
    }
    
    /**
     *  Set the datatype of a particular column.
     *
     *  @public
     *  @param string|int $field Column name or number to set.
     *  @param string $type The datatype or the column (eg. int|string|datetime).
     */
    public function set_data_type($field,$type) {
        if (is_int($field)) {
            $this->_init_headers($field);
            $this->parseAs[$field-1] = strtolower($type);
        } else {
            $found = $this->_get_header_index($field);
            if ($found !== false) {
                $this->parseAs[$found] = strtolower($type);
            } else {
                throw new Exception('Unknown column "'.$field.'".');
            }
        }
    }
    
    /**
     *  Set the header-name for a specific column.
     *
     *  @public
     *  @param int $index The column number (1-n).
     *  @param string $name The name to give to this column.
     */
    public function set_header($index,$name) {
        if (is_int($index)) {
            $this->_init_headers($index);
            $this->headers[$index-1] = $name;
        } else {
            $found = $this->_get_header_index($index);
            if ($found !== false) {
                $this->headers[$found] = $name;
            } else {
                throw new Exception('Unknown column "'.$index.'".');
            }
        }
    }
    
    /**
     *  Find the header index for the given value/string.
     *
     *  Will return the index (-1 as array starts from zero) if the supplied
     *  value is numeric.  If a string is given then a search will be done
     *  for the given header and its column-number returned.  If no index is
     *  found, false will be returned.
     *
     *  @private
     *  @param int|string $index The index/field-name to get the index of.
     *  @return int|boolean
     */
    private function _get_header_index($index) {
        if (is_numeric($index)) {
            return $index-1;
        } else {
            return array_search(field,$this->headers);
        }
    }
    
    /**
     *  Initialize the headers.
     *
     *  @private
     */
    private function _init_headers($end) {
        for ($i = 0; $i < $end; $i++) {
            if (!isset($this->headers[$i])) {
                $this->headers[$i] = 'Col'.$index;
            }
            if (!isset($this->parseAs[$i])) {
                $this->parseAs[$i] = 'text';
            }
        }
    }
    
    /**
     *  @public
     *  @param string $line One line from the log.
     *  @return array() The results of the parsing.
     */
    public function parse($line) {
        $matcher = $this->_get_line_splitting_regx();
        $array = preg_split($matcher,$line);
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
    
    private function _get_line_splitting_regx() {
        $matcher = '/\,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/';
        $matcher = str_replace(',',$this->comma,$matcher);
        $matcher = str_replace('"',$this->quotes,$matcher);
        return $matcher;
    }
    
    /**
     *  Convert an indexed array into one using the column headers.
     *
     *  @private
     *  @param array() $array The array to convert.
     *  @param array()
     */
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
    
    /**
     *  Convert the given value according to the type for the set column.
     *
     *  @private
     *  @param string $value The value to convert
     *  @param int $index The column, which the value belongs to.
     *  @return mixed The converted value.
     */
    private function _get_correct_type($value,$index) {
        $type = $this->_get_column_type($index);
        
        switch ($type) {
            case 'int': case 'integer': return $this->_get_int_value($value);
            case 'float': case 'double': case 'real': return (float) $value;
            case 'date': return $this->_get_date_value($value);
            case 'boolean': return $this->_get_boolean_value($value);
            default: return $value;
        }
    }
    
    /**
     *  Get the type for a given column.
     *
     *  @private
     *  @param int|string $index The column index.
     *  @return string The column type.
     */
    private function _get_column_type($index) {
        $index = $this->_get_header_index($index);
        if ($index === false) {
            throw new Exception('Unknown column "'.$index.'".');
        }
    
        if (isset($this->parseAs[$index])) {
            return $this->parseAs[$index];
        } else {
            return 'text';
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
    
    /**
     *  Parse the columns so that line-breaks and quotes are handled.
     *
     *  @private
     *  @param array() The array to parse.
     *  @return array()
     */
    private function _parse_columns($columns) {
        foreach ($columns as $key => $value) {
            $columns[$key] = preg_replace('/[\n\r\f]+\Z/','',$columns[$key]);
            $columns[$key] = str_replace(
                $this->quotes.$this->quotes,$this->quotes,$columns[$key]
            );
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
        $line = $this->rebuild_line($data);
        if ($line != '') {
            parent::write($line);
        }
    }
    
    public function rebuild_line($row) {
        $line = '';
        if (is_array($row)) {
            foreach ($row as $colValue) {
                $colValue = str_replace(
                    $this->quotes,$this->quotes.$this->quotes,$colValue
                );
                if ($line != '') {
                    $line .= $this->comma;
                }
                $line .= $this->quotes.$colValue.$this->quotes;
            }
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