<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *  Load settings from an XML settings file.
 *  
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.3
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Filesystem
 */
class Filesystem_File_Settings implements Filesystem_File_Object,ArrayAccess,Countable,Iterator {
    private $params = array();
    private $types = array(
        'include' => 'boolean', 'replace' => 'regx'
    );
    private $ignore = array('name', 'type', 'value');
    private $keys;
    private $position;
    private $fullpath;
    
    /**
     *  Load the parameters contained an XML settings file.
     *
     *  @public
     *  @param string $fullpath The file tom load
     *  @param string $method The file method to use.
     *  @return boolean
     */
    public function load($fullpath,$method='read') {
        $this->fullpath = $fullpath;
        $xml = simplexml_load_file($fullpath);
        
        foreach ($xml->param as $param) {
            $type = $this->_format_attribute_value($param,'type');
            $name = $this->_format_attribute_value($param,'name');
            $value = $this->_get_attribute_value($param['value'],$type);
            $this->_store_values($name,$value,$param);
        }
        
        $this->keys = array_keys($this->params);
        $this->position = 0;
    }
    
    /**
     *  Store the supplied value in the object parameter array against the given name.
     *
     *  Standard values are simply stored against the given name.  If an
     *  object of type Filesystem_File_Settings is supplied then the values
     *  are cycled through store under <NAMESPACE>.<VALUENAME>.  The namespace
     *  is the name supplied to the function.
     *
     *  @private
     *  @param string $name The name of the parameter (or namespace).
     *  @param string|int|boolean|ilesystem_File_Settings The value(s) to store.
     *  @param SimpleXMLElement $param The parameter to parse.
     */
    private function _store_values($name,$value,$param='') {
        if (is_object($value)) {
            foreach ($value as $childName => $childValue) {
                $this->params[$name.'.'.$childName] = $childValue;
            }
        } else {
            $this->params[$name] = $value;
            if ((!is_null($value)) && (!is_null($name))) {
                $this->params[$name] = $this->_get_data($param);
                $this->params[$name]['value'] = $value;
            }
        }   
    }
    
    /**
     *  Does a parameter exist?
     *
     *  @public
     *  @param string $offset The parameter to test for.
     *  @return boolean
     */
    public function offsetExists($offset) {
        $offset = strtolower(trim($offset));
        return (array_key_exists($offset,$this->params));
    }
    
    /**
     *  Get a parameter value
     *
     *  @public
     *  @param string $offset The parameter to get.
     *  @return array() The parameter.
     */
    public function offsetGet($offset) {
        if ($this->offsetExists($offset)) {
             $offset = strtolower(trim($offset));
             return $this->params[$offset];
        } else {
            throw new Exception('Array item "'.$offset.'" does not exist.');
        }
    }
    
    /**
     *  Set a specified parameter.
     *
     *  @public
     *  @param string $offset The parameter to set.
     *  @param string $value The new value to use.
     */
    public function offsetSet($offset,$value) {
        $offset = strtolower(trim($offset));
        $this->params[$offset] = $vlaue;
    }
    
    /**
     *  Unset a parameter.
     *
     *  @public
     *  @param string $offset The parameter to unset.
     */
    public function offsetUnset($offset) {
        if ($this->offsetExists($offset)) {
            unset($this->params[$offset]);
        } else {
            throw new Exception('Array item "'.$offset.'" does not exist.');
        }
        
    }
    
    /**
     *  How many parameters are stored?
     *
     *  @public
     *  @return int
     */
    public function count() {
        return count($this->params);
    }
    
    /**
     *  Get the current item.
     *
     *  @public
     *  @return array()
     */
    public function current() {
		$key = $this->keys[$this->position];
        return $this->params[$key];
    }
    
    /**
     *  Get the current position number/key.
     *
     *  @public
     *  @return string
     */
	public function key() {
		return $this->keys[$this->position];
	}
    
    /**
     *  Move the position counter forward by one.
     *
     *  @public
     */
	public function next() {
		++$this->position;
	}
    
    /**
     *  Reset the position counter to zero.
     *
     *  @public
     */
	public function rewind() {
		$this->position = 0;
	}
    
    /**
     *  Is the current position counter valid.
     *
     *  @public
     *  @return boolean
     */
	public function valid() {
		return isset($this->keys[$this->position]);
	}
    
    /**
     *  Export the loaded parameters in an array.
     *  
     *  @public
     *  @return array() All the loaded parameters
     */
    public function all() {
        return $this->params;
    }
    
    private function _get_data($attributes) {
        $data = array();
        $attributes = $this->_get_data_attributes($attributes);
        
        foreach ($attributes as $name => $value) {
            $name = strtolower(trim($name));
            
            $type = 'string';
            if (isset($this->types[$name])) {
                $type = $this->types[$name];
            }
            
            $data[$name] = $this->_get_attribute_value($value,$type,$name);
        }
        
        return $data;
    }
    
    /**
     *  Get attributes from simplexml element object.
     *
     *  Will grab the parameters, removing standard attributes that need to be
     *  obtained separately.
     *
     *  @private
     *  @param SimpleXMLElement $attributes The SimpleXML Element.
     *  @return array()  The parameters as an array.
     */
    private function _get_data_attributes($attributes) {
        $attributes = get_object_vars($attributes);
        $attributes = $attributes['@attributes'];
        foreach ($this->ignore as $ignore) {
            if (isset($attributes[$ignore])) {
                unset($attributes[$ignore]);
            }
        }
        return $attributes;
    }
    
    /**
     *  Format the attribute value.
     *
     *  Will lowercase and trim the attribute.  Other formatting rules can be
     *  applied via this method if required.
     *
     *  @private
     *  @param array() $attributes The attribute values.
     *  @param string $attributeName The attribute-name to format.
     *  @return string The formatted value.
     */
    private function _format_attribute_value($attributes,$attributeName) {
        if (isset($attributes[$attributeName])) {
            return strtolower(trim($attributes[$attributeName]));
        } else {
            return null;
        }
    }
    
    /**
     *  Get a value according to the supplied type.
     *
     *  Will grab a string as string, an integer as an integer, a boolean
     *  as a boolean, ...etc.
     *
     *  @private
     *  @param string $value The value to format.
     *  @param string $type The type of value to return
     *  @return string|int|boolean|Filesystem_File_Settings
     */
    private function _get_attribute_value($value,$type) {
        switch($type) {
            case 'string': return $value;
            case 'regx': return html_entity_decode($value,ENT_QUOTES);
            case 'boolean': return $this->_get_boolean_value($value);
            case 'int': return $this->_get_int_value($value);
            case 'date': return $this->_get_date_value($value);
            case 'src': return $this->_get_src_value($value);
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
     *  Return the a Filesystem_File_Settings  object  for the specified URL.
     *  
     *  @private
     *  @param string $value The value to use
     *  @return Filesystem_File_Settings
     */
    private function _get_src_value($value) {
        $file = new Filesystem_File($this->fullpath);
        $file->set_file($file->path.$value);
        $file->open('read','settings');
        return $file;
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
}
?>