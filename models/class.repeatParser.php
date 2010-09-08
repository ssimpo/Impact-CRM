<?php
/**
*		Repeat Parser Class
*		
*		@Author: Stephen Simpson
*		@Version: 0.0.1
*		@License: LGPL
*		
*/

class repeatParser {
	private $data = array();
	
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
	
	public function expand($start,$end) {
		$dates = array();
		
		//Find includes
		foreach ($this->data[repeatIncludeRules] as $RRULE) {
			array_push($dates,$this->expand_rule($RRULE,$start,$end));
		}
		array_push($dates,$this->data[repeatInclude]);
		$dates = array_unique($dates);
		
		//Find excludes
		foreach ($this->data[repeatExcludeRules] as $RRULE) {
			foreach ($this->expand_rule($RRULE,$start,$end) as $cdate) {
				$key = array_search($cdate,$dates);
				if ($key !== false) { unset($dates[$key]); }
			}
		}
		foreach ($this->data[repeatExclude] as $cdate) {
			$key = array_search($cdate,$dates);
			if ($key !== false) { unset($dates[$key]); }
		}
		
		return $dates;
	}
	
	protected function expand_rule($RRULE,$start,$until) {
		$cdate = 0;
		$dates = array();
		while ($cdate < $until) {
			
			
			if ($cdate > $start) { array_push($dates,$cdate); }
		}
		return $dates;
	}
}