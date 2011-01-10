<?php
/**
 *	Calendar.Journal class
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.3
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Calendar	
 */
class Calendar_Supercass {
	protected $data = array();
	
	function __construct() {
		$this->data[repeatIncludeRules] = array();
		$this->data[repeatExcludeRules] = array();
		$this->data[repeatInclude] = array();
		$this->data[repeatExclude] = array();
		$this->data[startDate] = '';
		$this->data[endDate] = '';
		$this->data[duration] = 0;
	}
	
	public function __call($name,$arguments) {
		$parts = explode('_',$name);
		if (count($parts) == 2) {
			switch ($parts[0]) {
				case 'set':
					if (count($arguments)>0) {
						$this->data[$parts[1]] = $arguments[0];
						return true;
					} else {
						return false;
					}
					break;
				case 'get':
					if (array_key_exists($parts[1],$this->data)) {
						return $this->data[$parts[1]];
					} else {
						print_r($this->data);
						return false;
					}
			}
		}
	}
	
	public function expand_repeats ($start,$end) {
		$repeatParser = $this->factory('repeatParser');
		if ($repeatParser) {
			$repeatParser->set_startDate($this->data[startDate]);
			$repeatParser->set_endDate($this->data[endDate]);
			$repeatParser->set_duration($this->data[duration]);
			$repeatParser->set_repeatIncludeRules($this->data[repeatIncludeRules]);
			$repeatParser->set_repeatExcludeRules($this->data[repeatExcludeRules]);
			$repeatParser->set_repeatInclude($this->data[repeatInclude]);
			$repeatParser->set_repeatExclude($this->data[repeatExclude]);
			
			$dates = $repeatParser->expand($start,$end);
			
			return $dates;
		} else {
			return $false;
		}
	}
	
	public static function factory($className) {
		$debug = debug_backtrace();
		$dir = dirname($debug[0][file]);
		
        if (include_once $dir.'/../class.'.str_replace('_','.',$className).'.php') {
			return new $className;
        } else {
            throw new Exception($className.' Class not found');
        }
    }

}