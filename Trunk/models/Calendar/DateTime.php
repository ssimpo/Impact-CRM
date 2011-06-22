<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *	Calendar.DateTime class
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.4
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Calendar
 *	@extends Calendar_Base
 */
class Calendar_DateTime Extends Base {
	const MINUTE = 60;
	const HOUR = 3600;
	const DAY = 86400;
	const WEEK = 604800;
	
	static private $monthNumbers = array(
		'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
		'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4,
		'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
		'may' => 5, 'jun' => 6, 'jul' => 7, 'aug' => 8,
		'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
		'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12
	);
	static private $monthLength = array(
		'jan' => 31, 'feb' => 28, 'mar' => 31, 'apr' => 30,
		'may' => 31, 'jun' => 30, 'jul' => 31, 'aug' => 31,
		'sep' => 30, 'oct' => 31, 'nov' => 30, 'dec' => 31
	);
	static private $monthName = array(
		'','Jan','Feb','Mar','Apr','May','Jun',
		'Jul','Aug','Sep','Oct','Nov','Dec'
	);
	static private $monthNameLong = array(
		'','January','February','March','April','May','June',
		'July','August','September','October','November','December'
	);
	private $epoc = 0;
	
	public function __construct($year='',$month='',$day='',$hours='',$minutes='',$seconds='') {
		$this->set_date($year,$month,$day,$hours,$minutes,$seconds);
	}
	
	public function set_date($year='',$month='',$day='',$hours='',$minutes='',$seconds='') {
		$now = getdate();
		
		$year = (($year=='')?$now['year']:$year);
		$month = (($month=='')?$now['mon']:$month);
		$day = (($day=='')?$now['mday']:$day);
		$hours = (($hours=='')?$now['hours']:$hours);
		$minutes = (($minutes=='')?$now['minutes']:$minutes);
		$seconds = (($seconds=='')?$now['seconds']:$seconds);
		$year = $this->_get_four_digit_year($year);
		$month = $this->_get_month_number($month);
		
		$this->epoc = mktime($hours,$minutes,$seconds,$month,$day,$year);
		return $this->epoc;
	}
	
	public function __get($name) {
		$date = getdate($this->epoc);
		
		switch (strtolower($name)) {
			case 'year': return $date['year'];
			case 'month': return $date['mon'];
			case 'monthname': return $date['month'];
			case 'day': return $date['mday'];
			case 'hours': return $date['hours'];
			case 'minutes': return $date['minutes'];
			case 'seconds': return $date['seconds'];
			case 'yearday': return $date['yday'];
			case 'weekday': return $date['weekday'];
			default:
				$name = '_get_'.I::uncamelize($name);
				return call_user_func(array($this,$name),array());
				break;
		}
	}
	
	public function __set($name,$value) {
		$name = '_set_'.I::uncamelize($name);
		call_user_func(array($this,$name),$value);
	}
	
	public function ajust($year=0,$month=0,$day=0,$hours=0,$minutes=0,$seconds=0) {
		$this->adjust_year($year);
		$this->adjust_month($month);
		$this->adjust_day($day);
		$this->adjust_hours($hours);
		$this->adjust_minutes($minutes);
		$this->adjust_seconds($seconds);
	}
	
	public function adjust_year($amount) {
		$this->_set_part('year',$this->year + $amount);
	}
	
	public function adjust_month($amount) {
		$year = $this->year;
		$month = $this->month;
		
		while ($factor != 0) {
			if ($factor > 0) {
				if (++$month > 12) {
					$month = 1;
					$year++;
				}
				$factor--;
			} else {
				if (--$month < 1) {
					$month = 12;
					$year--;
				}
				$factor++;
			}
			if ((($amount > 0) && ($factor < 0)) || (($amount < 0) & ($factor > 0))) {
				break;
			}
		}
		
		$this->year = $year;
		$this->month = $month;
	}
	
	public function adjust_week($amount) {
		$this->epoc += ($amount * self::WEEK);
	}
	
	public function adjust_day($amount) {
		$this->epoc += ($amount * self::DAY);
	}
	
	public function adjust_hours($amount) {
		$this->epoc += ($amount * self::HOUR);
	}
	
	public function adjust_minutes($amount) {
		$this->epoc += ($amount * self::MINUTE);
	}
	
	public function adjust_seconds($amount) {
		$this->epoc += $amount;
	}
	
	private function _set_part($part,$partName) {
		$date = getdate($this->epoc);
		$date[$partName] = $part;
		
		$this->epoc = mktime(
			$date['hours'],$date['minutes'],$date['seconds'],
			$date['mon'],$date['mday'],$date['year']
		);
		return $this->epoc;
	}
	
	private function _set_year($year) {
		$year = $this->_get_four_digit_year($year);
		return $this->_set_part($year,'year');
	}
	
	private function _set_month($month) {
		$month = $this->_get_month_number($month);
		$month = (($month < 0)?(12+$month):$month);
		return $this->_set_part($month,'mon');
	}
	
	private function _set_day($day) {
		$date = getdate($this->epoc);
		$month = $this->_get_month_number($date['mon']);
		$monthLength = $this->_get_month_length($date['year'],$month);
		$day = (($day < 0)?($monthLength+$day):$day);
		return $this->_set_part($day,'mday');
	}
	
	private function _set_hours($hours) {
		$hours = (($hours < 0)?(24+$hours):$hours);
		return $this->_set_part($hours,'hours');
	}
	
	private function _set_minutes($minutes) {
		$minutes = (($minutes < 0)?(60+$minutes):$minutes);
		return $this->_set_part($minutes,'minutes');
	}
	
	private function _set_seconds($seconds) {
		$seconds = (($seconds < 0)?(60+$seconds):$seconds);
		return $this->_set_part($seconds,'seconds');
	}
	
	private function _set_year_day($yearDay) {
		//STUB
	}
	
	private function _get_week() {
		//STUB
	}
	
	private function _set_week($week) {
		//STUB
	}
	
	private function _get_week_day_no() {
		// STUB
	}
	
	private function _set_week_day($weekDay) {
		//STUB
	}
	
	private function _get_month_length($year,$month){
		$month = strtolower(self::$monthName[$month]);
		
		if ($month == 'feb') {
			if ($this->_is_leap_year($year)) {
				return 29;
			} else {
				return 28;
			}
		} else {
			return $length = self::$monthLength[$month];
		}
	}
	
	private function _get_month_number($month){
		if (is_numeric($month)) {
			return $month;
		}
		
		return self::$monthNumbers[strtolower($month)];
	}
	
	/**
	 *	Is the specified year a leap year?
	 *
	 *	@private
	 *	@param int $year The 4-digit year, or 2-digit year where <50 is in the 2000s and >50 in 1900s
	 *	@return boolean
	 */
	private function _is_leap_year($year) {
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
	private function _get_four_digit_year($year) {
		if ($year < 50) {
			return ($year + 2000);
		}
		if ($year < 100) {
			return ($year + 1900);
		}
		return $year;
	}
}