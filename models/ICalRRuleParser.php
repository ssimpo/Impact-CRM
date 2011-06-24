<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *	iCal Repeat Parser Class
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.3
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Calendar
 */
class ICalRRuleParser extends Base {
	private $dateParser = null;
	static private $modifiers = array(
		'BYMONTH'=>'month', 'BYWEEKNO'=>'week', 'BYYEARDAY'=>'yearday',
		'BYMONTHDAY'=>'day','BYDAY'=>'', 'BYHOUR'=>'hours',
		'BYMINUTE'=>'minutes', 'BYSECOND'=>'seconds','BYSETPOS'=>''
	);
	static private $intervals = array(
		'SECONDLY'=>'seconds', 'MINUTELY'=>'minutes', 'HOURLY'=>'hours',
		'DAILY'=>'day','WEEKLY'=>'week', 'MONTHLY'=>'month',
		'YEARLY'=>'year'
	);
	
	public function __construct() {
		$this->dateParser = new DateParser();
	}
	
	public function parse($rrule,$start='') {
		$parsedRrule = $rrule;
		
		if (!is_array($rrule)) {
			$parsedRrule = $this->_split_rrule($rrule);
		}
		$this->_intialize_rrule($parsedRrule);
		
		$dates = array();
		$date = $rrule['DTSTART'];
		while ((count($dates) < $rrule['COUNT']) && ($date->epoc <= $rrule['UNTIL']->epoc)) {
			$cdate = $this->_next_interval($rrule['DTSTART'],$rrule);
			$dates = array_merge($dates,$this->_get_next($cdate,$rrule));
			$dates = usort($dates,arry($this,'_sort_dates'));
			$test = array();
			
			for ($i = 0; $i < count($dates); $i++) {
				$date = $dates[$i];
				
				if ($i > $rrule['COUNT']) {
					break;
				} elseif (isset($test[$date->epoc])) {
					unset($dates[$i]);
				} elseif ($date->epoc > $rrule['UNTIL']->epoc) {
					unset($dates[$i]);
				} else {
					$test[$date->epoc] = true;
				}
			}	
		}
		
		
		if (count($dates) > $rrule['COUNT']) {
			$dates = array_slice($dates,0,$rrule['COUNT']);
		}
		
		return $dates;
	}
	
	/**
	 *	Calculate the next date according to the recurrance rule.
	 *
	 *	Given the iCal RRULE and the date of the last recurrance, calculate
	 *	the next date in the sequence.
	 *
	 *	@protected
	 *	@param DateTime $cdate The Unix date.
	 *	@param array() $rrule The iCal RRULE parsed into an array.
	 *	@return DateTime The next Unix date.
	 */
	private function _next_interval($cdate,$rrule) {
		$newDate = clone $cdate;
		return $newDate->adjust(
			self::$intervals[$rrule['FREQ']],
			$rrule['INTERVAL']
		);
	}
	
	private function _get_next($cdate,$rrule) {
		$cdates = $cdate;
		foreach (self::$modifiers as $modifier => $modifierValue) {
			if (array_key_exists($modifier,$rrule)) {
				$parts = $rrule[$modifier];
				$cdates = $this->_next_generic($cdates,$modifier,$modifierValue);
			}
		}
		
		return $cdates;
	}
	
	private function _next_generic($cdate,$parts,$partName) {
		$dates = $this->_make_array($cdate);
		$newDates = array();
		
		foreach ($parts as $part) {
			foreach ($dates as $date) {
				$newDate = clone $date;
				$newDate->set($partName,$part);
				array_push($newDates,$newDate);
			}
		}
		return $newDates;
	}
	
	private function _intialize_rrule($rrule) {
		$cdate = $this->dateParser->parser($rrule['DTSTART']);
		if (!array_key_exists('INTERVAL',$rrule)) {
			$rrule['INTERVAL'] = 1;
		}
		if (!array_key_exists('COUNT',$rrule)) {
			// Safety measure
			$rrule['COUNT'] = 1000;
		}
		if (!array_key_exists('UNTIL',$rrule)) {
			// Safety measure
			$now = new Calendar_DateTime();
			$now->adjust_year(10);
			$rrule['UNTIL'] = $now;
		} else {
			$rrule['UNTIL'] = $this->parser($rrule['UNTIL']);
		}
		
		return $rrule;
	}
	
	/**
	 *	Split an iCal rule into it's parts.
	 *
	 *	@private
	 *	@param string $rrule The rule to split-up.
	 *	@return array() The resulting array.
	 */
	private function _split_rrule($rrule) {
		$result = array();
		
		$parts = explode(';',strtoupper($rrule));
		foreach ($parts as $part) {
			$item = explode('=',$part);
			if (count($item) == 2) {
				$result[trim($item[0])] = $this->_get_rrule_values($part);
			} else {
				throw new Exception($rrule.' is not a valid iCal RRULE.');
			}
		}
		
		return $result;
	}
	
	/**
	 *	Grab the value(s) from an iCal RRULE part.
	 *
	 *	@private
	 *	@param string $rrule The RRULE part.
	 *	@return string|int|array() The value(s)
	 */
	private function _get_rrule_values($rrule) {
		$values = explode('=',$rrule);
		
		if (count($values) == 2) {
			$values = array_pop($values);
			
			if ($this->_contains($values,',')) {
				$values = explode(',',$values);
				for ($i = 0; $i < count($values); $i++) {
					$values[$i] = $this->_get_string_or_number($values[$i]);
				}
				return $values;
			} else {
				return $this->_get_string_or_number($values);
			}
		} else {
			throw new Exception($rrule.' is not valid as part of an iCal RRULE.');
		}
	}
	
	/**
	 *	If supplied text is numeric then return a number, otherwise a string.
	 *
	 *	@private
	 *	@param string $string The string to test and return.
	 *	@return string|int|float
	 */
	private function _get_string_or_number($string) {
		if (is_numeric($string)) {
			if ($this->_contains($string,'.')) {
				return (float) $string;
			} else {
				return (int) $string;
			}
		} else {
			return $string;
		}
		return ((is_numeric($string))?(int) $string:$string);
	}
	
	/**
	 *	Is the one snippet of text contained within another.
	 *
	 *	@private
	 *	@param String $text1 The string to search within.
	 *	@param String $text2 The string to search for.
	 *	@return Boolean
	*/
	private function _contains($text1,$text2) {
		return ((stristr($text1,$text2) !== false) ? true:false);
	}
	
	/**
	 *	Make the passed paramater an array if it isn't already one.
	 *
	 *	@private
	 *	@param mixed $item The variable to turn into an array.
	 *	@return array()
	 */
	private function _make_array($item) {
		if (is_array($item)) {
			return $item;
		}
		return array($item);
	}
	
	private function _sort_dates($a,$b) {
		if ($a->epoc == $b->epoc) {
			return 0;
		}
		return ($a->epoc < $b->epoc) ? -1 : 1;
	}
}