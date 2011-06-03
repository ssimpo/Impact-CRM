<?php
/**
*	Calendar.Base class
*
*	Just as the base-class for Calendar objects (like Calendar_Event or
*	Calendar_Journal).
*		
*	@author Stephen Simpson <me@simpo.org>
*	@version 0.0.5
*	@license http://www.gnu.org/licenses/lgpl.html LGPL
*	@package Calendar
*/
class Calendar_Base Extends ImpactBase {
	protected $data = array();
	protected static $dateTagLookup = array(
		'startDate' => true, 'endDate' => true,
		'dateStamp' => true, 'createdDate' => true,
		'lastModifiedDate' => true
	);
	public $calendar = null;

	public function __construct() {
		
	}

	public function __call($name,$arguments) {
		$parts = explode('_',$name);
		if (count($parts) > 1) {
			$action = array_shift($parts);
			$tag = I::camelize(implode('_',$parts));
			
			switch ($action) {
				case 'set':
					if (count($arguments) > 0) {
						if (array_key_exists($tag,self::$dateTagLookup)) {
							$this->set_date($tag, $arguments);
						} else {
							$this->data[$tag] = $arguments[0];	
						}
						return true;
					} else {
						return false;
					}
					break;
				case 'get':
					if (array_key_exists($tag,$this->data)) {
						return $this->data[$tag];
					} else {
						return false;
					}
			}
		}
	}
	
	private function _get_timezone($date) {
		$timezone = '';
		if (count($date) == 2) {
			if (is_array($date[1])) {
				if (array_key_exists('TZID',$date[1])) {
					$timezone = $date[1]['TZID'];
				}
			}
		}
		
		$timezone = md5($timezone.'@'.$this->get_cal_id());
		$timezone = $this->calendar->get($timezone);
		
		return $timezone;
	}
	
	public function set_date($name, $date) {
		$timezone = $this->_get_timezone($date);
		$date = $date[0];
		$DateParser = $this->factory('DateParser');
		$utc_date = $DateParser->convert_date($date,'',$timezone);
		$this->data[$name] = $utc_date;
	}

	public function expand_repeats($start,$end) {
		$Repeat_Parser = $this->factory('Repeat_Parser');
		if ($Repeat_Parser) {
			$Repeat_Parser->set_start_date($this->data['startDate']);
			$Repeat_Parser->set_end_date($this->data['endDate']);
			$Repeat_Parser->set_duration($this->data['duration']);
			$Repeat_Parser->set_repeat_include_rules($this->data['repeatIncludeRules']);
			$Repeat_Parser->set_repeat_exclude_rules($this->data['repeatExcludeRules']);
			$Repeat_Parser->set_repeat_include($this->data['repeatInclude']);
			$Repeat_Parser->set_repeat_exclude($this->data['repeatExclude']);
			
			$dates = $Repeat_Parser->expand($start,$end);
			
			return $dates;
		} else {
			return $false;
		}
	}

}
?>