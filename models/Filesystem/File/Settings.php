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
class Filesystem_File_Settings implements Filesystem_File_Object {
    private $params = array();
    private $types = array(
        'include' => 'boolean'
    );
    private $ignore = array(
        'type' => true, 'value' => true
    );
    
    public function load($fullpath,$method) {
        $file = new FileSystem_File($fullpath);
        $file->open();
        
        $xml = simplexml_load_string($file->all());
        foreach ($xml->param as $param) {
            $name = $this->_format_attribute_value($param,'name');
            $value = $this->_get_attribute_value($param,'name');
            
            if ((!is_null($value)) && (!is_null($name))) {
                $this->params[$name] = $this->_get_data($param);
                $this->params[$name]['value'] = $value;
            }
        }
    }
    
    public function all() {
        return $this->params;
    }
    
    private function _get_data($attributes) {
        $data = array();
         
        foreach ($attributes as $attName => $attValue) {
            $attName = strtolower(trim($attName));
            
            if ((isset($this->types[$attName])) && (!isset($this->ignore[$attName]))) {
                switch ($this->types[$attName]) {
                    case 'int':
                        $data[$attName] = $this->_get_int_value($attValue);
                        break;
                    case 'boolean':
                        $data[$attName] = $this->_get_boolean_value($attValue);
                        break;
                    case 'regx':
                        $data[$attName] = str_replace('&quot;','"',$attValue);
                        $data[$attName] = str_replace('&amp;','&',$attValue);
                        break;
                    default:
                        $data[$attName] = $attValue;
                        break;
                }
            }
        }
        
        return $data;
    }
    
    private function _format_attribute_value($attributes,$attributeName) {
        if (isset($attributes[$attributeName])) {
            return strtolower(trim($attributes[$attributeName]));
        } else {
            return null;
        }
    }
    
    private function _get_attribute_value($attributes) {
        if ((!isset($attributes['type'])) || (!isset($attributes['value']))) {
            return null;
        }
        
        $type = $this->_format_attribute_value($attributes,'type');
        $value = $attributes['value'];
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
    
    
    
}
?>