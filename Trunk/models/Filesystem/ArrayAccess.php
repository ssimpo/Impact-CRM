<?php
defined('DIRECT_ACCESS_CHECK') or die;

/*
 *	File handling class
 *
 *	File Open/Close and parsing operations.  Will open files, navigate through
 *	them and parse them according to installed sub-classes.
 *	
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.7
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Filesystem
 */
abstract class Filesystem_ArrayAccess extends Filesystem implements ArrayAccess,Countable,Iterator {
	
	public function offsetExists($offset) {
		if (method_exists($this->parser,'offsetExists')) {
			return $this->parser->offsetExists($offset);
		} else {
			throw new Exception('The array function "offsetExists" is not available.');
		}
	}
    
	public function offsetGet($offset) {
		if (method_exists($this->parser,'offsetGet')) {
			return $this->parser->offsetGet($offset);
		} else {
			throw new Exception('The array function "offsetGet" is not available.');
		}
	}
    
	public function offsetSet($offset,$value) {
		if (method_exists($this->parser,'offsetSet')) {
			return $this->parser->offsetSet($offset,$value);
		} else {
			throw new Exception('The array function "offsetSet" is not available.');
		}
	}
    
	public function offsetUnset($offset) {
		if (method_exists($this->parser,'offsetUnset')) {
			return $this->parser->offsetUnset($offset);
		} else {
			throw new Exception('The array function "offsetUnset" is not available.');
		}
	}
    
	public function count() {
		if (method_exists($this->parser,'count')) {
			return $this->parser->count();
		} else {
			throw new Exception('The array function "count" is not available.');
		}
	}
    
	public function current() {
		if (method_exists($this->parser,'current')) {
			return $this->parser->current();
		} else {
			throw new Exception('The array function "current" is not available.');
		}
	}
    
	public function key() {
		if (method_exists($this->parser,'key')) {
			return $this->parser->key();
		} else {
			throw new Exception('The array function "key" is not available.');
		}
	}
    
	public function next() {
		if (method_exists($this->parser,'next')) {
			return $this->parser->next();
		} else {
			throw new Exception('The array function "next" is not available.');
		}
	}
	public function rewind() {
		if (method_exists($this->parser,'rewind')) {
			return $this->parser->rewind();
		} else {
			throw new Exception('The array function "rewind" is not available.');
		}
	}
    
	public function valid() {
		if (method_exists($this->parser,'valid')) {
			return $this->parser->valid();
		} else {
			throw new Exception('The array function "valid" is not available.');
		}
	}
    
}
?>