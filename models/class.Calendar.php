<?php
/**
*		Calendar class
*		
*		@Author: Stephen Simpson
*		@Version: 0.0.1
*		@License: LGPL
*		
*/
include_once 'Calendar/superclass.Calendar.php';

class Calendar {
	protected $objects = array();
	
	public function __call($name,$arguments) {
		$parts = explode('_',strtolower($name));
		if (count($parts) == 2) {
			switch ($parts[0]) {
				case 'add':
					$id = $this->_rnd_string();
					$this->objects[$id] = $this->factory('Calendar_'.$parts[1]);
					$this->objects[$id]->set_id($id);
					return $this->objects[$id];
					break;
			}
		}
	}
	
	protected function _rnd_string () {
		return md5(chr(rand(1,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)));
	}
	
	public static function factory($className) {
		$debug = debug_backtrace();
		$dir = dirname($debug[0][file]);
		
        if (include_once $dir.'/Calendar/class.'.str_replace('_','.',$className).'.php') {
            return new $className;
        } else {
            throw new Exception($className.' Class not found');
        }
    }
	
	
}