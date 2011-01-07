<?php
/**
*		Main database class
*		
*		@Author: Stephen Simpson
*		@Version: 0.0.1
*		@License: LGPL
*		
*/
class Impact_Superclass {
	public static function factory($className) {
		$dir = self::get_include_directory();
		
        if (include_once '/class.'.str_replace('_','.',$className).'.php') {
			return new $className;
        } else {
            throw new Exception($className.' Class not found');
        }
    }
	
	public function get_include_directory() {
		$debug = debug_backtrace();
		return dirname($debug[0][file]);
	}
	
	public function _add_square_brakets($txt) {
		$txt = '['.str_replace(',','],[',$txt).']';
		$txt = str_replace('[[','[',$txt);
		$txt = str_replace(']]',']',$txt);
		$txt = str_replace('[ ','[',$txt);
		return str_replace(' ]',']',$txt);
	}
}