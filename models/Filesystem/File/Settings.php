<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *  Lotus/IBM Domino Log Interpreter Clsss
 *  
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Analytics
 */
class Filesystem_File_Settings implements Filesystem_File_Object,ArrayAccess,Countable,Iterator {
    private $params = array();
    private $types = array(
        'include' => 'boolean'
    );
    private $ignore = array(
        'type' => true, 'value' => true
    );
    private $keys;
    private $position;
    private $fullpath;
    
    public function load($fullpath,$method) {
        $this->fullpath = $fullpath;
        $xml = simplexml_load_file($fullpath);
        
        foreach ($xml->param as $param) {
            $type = $param['type'];
            $name = $this->_format_attribute_value($param,'name');
            $value = $this->_get_attribute_value($param['value'],$type);
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
        
        $this->keys = array_keys($this->params);
        $this->position = 0;
    }
    
    public function offsetExists($offset) {
        $offset = strtolower(trim($offset));
        return (array_key_exists($offset,$this->params));
    }
    
    public function offsetGet($offset) {
        if ($this->offsetExists($offset)) {
             $offset = strtolower(trim($offset));
             return $this->params[$offset];
        } else {
            throw new Exception('Array item "'.$offset.'" does not exist.');
        }
    }
    
    public function offsetSet($offset,$value) {
        $offset = strtolower(trim($offset));
        $this->params[$offset] = $vlaue;
    }
    
    public function offsetUnset($offset) {
        if ($this->offsetExists($offset)) {
            unset($this->params[$offset]);
        } else {
            throw new Exception('Array item "'.$offset.'" does not exist.');
        }
        
    }
    
    public function count() {
        return count($this->params);
    }
    
    public function current() {
		$key = $this->keys[$this->position];
        return $this->params[$key];
    }
    
	public function key() {
		return $this->keys[$this->position];
	}
    
	public function next() {
		++$this->position;
	}
    
	public function rewind() {
		$this->position = 0;
	}
    
	public function valid() {
		return isset($this->keys[$this->position]);
	}
    
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
    
    private function _get_data_attributes($attributes) {
        $attributes = get_object_vars($attributes);
        $attributes = $attributes['@attributes'];
        unset($attributes['type']);
        unset($attributes['name']);
        unset($attributes['value']);
        return $attributes;
    }
    
    private function _format_attribute_value($attributes,$attributeName) {
        if (isset($attributes[$attributeName])) {
            return strtolower(trim($attributes[$attributeName]));
        } else {
            return null;
        }
    }
    
    private function _get_attribute_value($value,$type,$name='') {
        switch($type) {
            case 'string':
                return $value;
            case 'regx':
                $value = str_replace('&quot;','"',$value);
                $value = str_replace('&amp;','&',$value);
                return $value;
            case 'boolean':
                return $this->_get_boolean_value($value);
            case 'int':
                return $this->_get_int_value($value);
            case 'src':
                return $this->_get_src_value($value);
                break;
            default:
                return $value;
                break;
        }
    }
    
    private function _get_int_value($value) {
        $value = trim($value);
        
        if (!is_numeric($value)) {
            return 0;
        } else {
            return (int) $value;
        }
    }
    
    private function _get_boolean_value($value) {
        switch (strtolower($value)) {
			case 'true': case 'yes': case 'on':
				return true;
			case 'false': case 'no': case 'off':
				return false;
		}
        
        return false;
    }
    
    private function _get_src_value($value) {
        $file = new Filesystem_File($this->fullpath);
        $file->set_file($file->path.$value);
        $file->open('read','settings');
        return $file;
    }
    
}
?>