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
	private $modifiers = array(
		'BYMONTH', 'BYWEEKNO', 'BYYEARDAY', 'BYMONTHDAY',
		'BYDAY', 'BYHOUR', 'BYMINUTE', 'BYSECOND',
		'BYSETPOS'
	);
	private $inverval_period = array(
		'SECONDLY' => 1, 'MINUTELY' => 60, 'HOURLY' => 3600,
		'DAILY' => 86400, 'WEEKLY' => 604800
	);
	private $months = array(
		'JAN' => 31, 'FEB' => 28, 'MAR' => 31, 'APR' => 30,
		'MAY' => 31, 'JUN' => 30, 'JUL' => 31, 'AUG' => 31,
		'SEP' => 30, 'OCT' => 31, 'NOV' => 30, 'DEC' => 31
	);
	
	
	/**
	 *	Return Unix timestamp according to the supplied string.
	 *
	 *	@note If no date is given or it is blank then the current date is returned.
	 *
	 *	@protected
	 *	@param string $date  The date-string.
	 *	@return TimeDate
	 */
	protected function _get_date($date='') {
		$parsedDate = $date;
		
		if (is_string($parsedDate)) {
			if ($parsedDate != '') {
				if ($this->dateParser == null) {
					$this->dateParser = $this->factory('DateParser');
				}
				$parsedDate = $this->dateParser->parse($parsedDate,'','');
			} else {
				$parsedDate = time();
			}
		}
		
		return $parsedDate;
	}
	
	protected function _get_next($cdate,$rrule) {
		if (array_key_exists('INTERVAL',$rrule)) {
			$cdate = $this->_next_interval($cdate,$rrule);
		}
		foreach ($this->modifiers as $modifier => $modifierValue) {
			if (array_key_exists($modifier,$rrule)) {
				$functionName = '_next_'.strtolower($modifier);
				$cdate = call_user_func(array($this,$functionName),$cdate,$rrule);
			}
		}
		return $cdate;
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
		$period = $rrule['FREQ'];
		
		if (($period == 'MONTHLY') || ($period == 'YEARLY')) {
			// Months have different lengths and years can be different
			// depending on leap years
			$addSeconds = $cdate;
			for ($i = 0; $i < $rrule['INTERVAL']; $i++) {
				$addSeconds += $this->_get_seconds_in_period($period,$addSeconds);
			}
			return $addSeconds;
		} else {
			$addSeconds = $this->_get_seconds_in_period($period,$cdate);
			$addSeconds *= $rrule['INTERVAL'];
			return ($cdate + $addSeconds);
		}
	}
	
	/**
	 *	Get the number of seconds in the current FREQ.
	 *
	 *	The number of seconds to add to a date in our to get the next
	 *	recurrance according to RRULE (before INTERVAL is taken into account).
	 *
	 *	@protected
	 *	@param string $period The period (FREQ) type (eg. DAILY, MONTLY, YEARLY, ...etc).
	 *	@param DateTime $cdate The Unix date
	 *	@return int The number of seconds.
	 */
	protected function _get_seconds_in_period($period,$cdate) {
		if ($period == 'MONTHLY') {
			return $this->_get_seconds_in_month_period($cdate);
		} elseif ($period == 'YEARLY') {
			return $this->_get_seconds_in_year_period($cdate);
		} elseif (array_key_exists($period,$this->inverval_period)) {
			return $this->inverval_period[$period];
		}
		
		throw new Exception('Unknown FREQ value in iCal RRULE.');
	}
	
	/**
	 *	Calculate the number of seconds for one month to be added to a date.
	 *
	 *	@note Method takes into concideration leap years
	 *
	 *	@protected
	 *	@param DateTime $cdate A Unix date.
	 *	@return int The number of seconds.
	 */
	protected function _get_seconds_in_month_period($cdate) {
		$date = getdate($cdate);
		$month = strtoupper(substr($date['month'],0,3));
		$seconds_in_day = $this->inverval_period['DAILY'];
		
		if ($date['mon'] != 2) {
			return ($this->months[$month] * $seconds_in_day);
		} else {
			// February and leap-year work-around
			if ($this->_is_leap_year($date['year'])) {
				return (29 * $seconds_in_day);
			} else {
				return ($this->months[$month] * $seconds_in_day);
			}
		}
	}
	
	/**
	 *	Calculate the number of seconds for one year to be added to a date.
	 *
	 *	@note Method takes into concideration leap years
	 *
	 *	@protected
	 *	@param DateTime $cdate A Unix date.
	 *	@return int The number of seconds.
	 */
	protected function _get_seconds_in_year_period($cdate) {
		$date = getdate($cdate);
		$seconds_in_day = $this->inverval_period['DAILY'];
		$seconds_in_year = ($seconds_in_day * 365);
		
		if ($date['mon'] > 2) {
			// If you a going over a Feb 29th (next year) then an
			// extra day needs adding
			if ($this->_is_leap_year($date['year']+1)) {
				$seconds_in_year += $seconds_in_day;
			}
		}
		
		if ($date['mon'] <= 2) {
			// If you a going over a Feb 29th (this year) then an
			// extra day needs adding
			if ($this->_is_leap_year($date['year'])) {
				$seconds_in_year += $seconds_in_day;
			}
		}
		
		return $seconds_in_year;
	}
	
	protected function _next_bysecond($cdate,$rrule) {
		$seconds = $rrule['BYSECOND'];
		$dates = $this->_make_array($cdate);
		$newdates = array();
		
		foreach ($seconds as $second) {
			foreach ($dates as $date) {
				$dateArray = getdate($date);
				$newdate = mktime(
					$seconds,$dateArray['minutes'],$dateArray['hours'],
					$dateArray['mday'],$dateArray['mday'],$dateArray['year']
				);
				array_push($newdates,$newdate);
			}
		}
		
		return $newdates;
	}
	
	protected function _next_byminute($cdate,$rrule) {
		$minutes = $rrule['BYMINUTE'];
		$dates = $this->_make_array($cdate);
		$newdates = array();
		
		foreach ($minutes as $minute) {
			foreach ($dates as $date) {
				$dateArray = getdate($date);
				$newdate = mktime(
					$dateArray['seconds'],$minute,$dateArray['hours'],
					$dateArray['mday'],$dateArray['mday'],$dateArray['year']
				);
				array_push($newdates,$newdate);
			}
		}
		
		return $newdates;
	}
	
	protected function _next_byhour($cdate,$rrule) {
		$hours = $rrule['BYHOUR'];
		$dates = $this->_make_array($cdate);
		$newdates = array();
		
		foreach ($hours as $hour) {
			foreach ($dates as $date) {
				$dateArray = getdate($date);
				$newdate = mktime(
					$dateArray['seconds'],$dateArray['minutes'],$hour,
					$dateArray['mday'],$dateArray['mday'],$dateArray['year']
				);
				array_push($newdates,$newdate);
			}
		}
		
		return $newdates;
	}
	
	protected function _next_byday($cdate,$rrule) {
		$days = $rrule['BYDAY'];
		$dates = $this->_make_array($cdate);
		$newdates = array();
	}
	
	protected function _next_bymonthday($cdate,$rrule) {
		$days = $rrule['BYMONTHDAY'];
		$dates = $this->_make_array($cdate);
		$newdates = array();
		
		foreach ($days as $day) {
			foreach ($dates as $date) {
				$dateArray = getdate($date);
				$newdate = mktime(
					$dateArray['seconds'],$dateArray['minutes'],$dateArray['hours'],
					$day,$dateArray['mday'],$dateArray['year']
				);
				array_push($newdates,$newdate);
			}
		}
		
		return $newdates;
	}
	
	protected function _next_byyearday($cdate,$rrule) {
		$days = $rrule['BYYEARDAY'];
		$dates = $this->_make_array($cdate);
		$newdates = array();
	}
	
	protected function _next_byweekno($cdate,$rrule) {
		$weeks = $rrule['BYWEEKNO'];
		$dates = $this->_make_array($cdate);
		$newdates = array();
	}
	
	protected function _next_bymonth($cdate,$rrule) {
		$months = $rrule['BYMONTH'];
		$dates = $this->_make_array($cdate);
		$newdates = array();
		
		foreach ($months as $month) {
			foreach ($dates as $date) {
				$dateArray = getdate($date);
				$newdate = mktime(
					$dateArray['seconds'],$dateArray['minutes'],$dateArray['hours'],
					$month,$dateArray['mday'],$dateArray['year']
				);
				array_push($newdates,$newdate);
			}
		}
		
		return $newdates;
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
	
	/**
	 *	Is the specified year a leap year?
	 *
	 *	@private
	 *	@param int $year The 4-digit year, or 2-digit year where <50 is in the 2000s and >50 in 1900s
	 *	@return boolean
	 */
	protected function _is_leap_year($year) {
		$testYear = $this->_get_four_digit_year($year);
		
		if (($testYear%400) == 0) {
			return true;
		}
		if (($testYear%100) == 0) {
			return false;
		}
		if (($testYear%4) == 0) {
			return true;
		}
		return false;
	}
	
	/**
	 *	Get the four digigit year from two digit one.
	 *
	 *	Years 50-99 are set to 1950-1999, whilst years 00-49 are set to
	 *	2000-2049.  Four digit years are returned unchanged.
	 *
	 *	@protected
	 *	@param int $year The two or four digit year.
	 *	@return int The year adjusted to four digits.
	 */
	protected function _get_four_digit_year($year) {
		if ($year < 50) {
			return ($year + 2000);
		}
		if ($year < 100) {
			return ($year + 1900);
		}
		return $year;
	}
}