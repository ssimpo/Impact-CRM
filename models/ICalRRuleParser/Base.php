<?php
if (!defined('DIRECT_ACCESS_CHECK')) {
	die('Direct access is not allowed');
}

/**
 *	ICalRRuleParser.Base class
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.3
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Calendar
 *
 *	@todo What happens if a day repeats monthly on 31st of the month in a 30-day month/
 *	@todo What happens if a day repeats on February 29th?
 */
abstract class ICalRRuleParser_Base Extends Base {
	protected $dateParser = null;
	static private $modifiers = array(
		'BYMONTH'=>'month', 'BYWEEKNO'=>'week', 'BYYEARDAY'=>'yearday',
		'BYMONTHDAY'=>'day','BYDAY'=>'', 'BYHOUR'=>'hours',
		'BYMINUTE'=>'minutes', 'BYSECOND'=>'seconds','BYSETPOS'=>''
	);
	static protected $intervals = array(
		'SECONDLY'=>'seconds', 'MINUTELY'=>'minutes', 'HOURLY'=>'hours',
		'DAILY'=>'day','WEEKLY'=>'week', 'MONTHLY'=>'month',
		'YEARLY'=>'year'
	);
	
	/**
	 *	Parse an iCal RRULE
	 *	
	 *	@public
	 *	@param array() $rrule The iCal RRULE broken-down into it's componants.
	 *	@param DateTime $start The iCal DTSTART for the RRULE.
	 *	@return
	 */
	public function parse($rrule) {
		$rrule = $this->intialize_rrule($rrule);
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
	
	protected function _sort_dates($a,$b) {
		if ($a->epoc == $b->epoc) {
			return 0;
		}
		return ($a->epoc < $b->epoc) ? -1 : 1;
	}
	
	protected function intialize_rrule($rrule) {
		$this->dateParser = new DateParser();
		
		$cdate = $this->parser($rrule['DTSTART']);
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
	protected function _next_interval($cdate,$rrule) {
		$newDate = clone $cdate;
		return $newDate->adjust(
			self::$intervals[$rrule['FREQ']],
			$rrule['INTERVAL']
		);
	}
	
	protected function _get_next($cdate,$rrule) {
		$cdates = $cdate;
		foreach (self::$modifiers as $modifier => $modifierValue) {
			if (array_key_exists($modifier,$rrule)) {
				$parts = $rrule[$modifier];
				$cdates = $this->_next_generic($cdates,$modifier,$modifierValue);
			}
		}
		
		return $cdates;
	}
	
	protected function _next_generic($cdate,$parts,$partName) {
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
	
	/**
	 *	Make the passed paramater an array if it isn't already one.
	 *
	 *	@protected
	 *	@param mixed $item The variable to turn into an array.
	 *	@return array()
	 */
	protected function _make_array($item) {
		if (is_array($item)) {
			return $item;
		}
		return array($item);
	}
}