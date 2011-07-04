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
	static private $weekdays = array(
		'SU','MO','TU','WE','TH','FR','SA',
		'SU'=>0,'MO'=>1,'TU'=>2,'WE'=>3,'TH'=>4,'FR'=>5,'SA'=>6
	);
	public $epoc = 0;
	public $printFormat = 'l jS F Y';
	private $wkstart = 'MO';
	
	public function __construct($year=false,$month=false,$day=false,$hours=false,$minutes=false,$seconds=false) {
		$this->set_date($year,$month,$day,$hours,$minutes,$seconds);
	}
	
	/**
	 *	Set the date/time.
	 *
	 *	@public
	 *	@param int $year The year to set date to (either 2 or 4 digit).
	 *	@param int|string $month The month to set date to (either 1-2 digit or month name, eg. Jan or January).
	 *	@param int $day The day to set date to.
	 *	@param int $hours The hour to set date to.
	 *	@param int $minutes The minute to set date to.
	 *	@param int $seconds The second to set date to.
	 *	@return int The no. of seconds since the Unix epoc for the set date.
	 */
	public function set_date($year=false,$month=false,$day=false,$hours=false,$minutes=false,$seconds=false) {
		$now = getdate();
		
		$year = (($year===false)?$now['year']:$year);
		$year = $this->_get_four_digit_year($year);
		$month = (($month===false)?$now['mon']:$month);
		$month = $this->_get_month_number($month);
		$day = (($day===false)?$now['mday']:$day);
		$hours = (($hours===false)?$now['hours']:$hours);
		$minutes = (($minutes===false)?$now['minutes']:$minutes);
		$seconds = (($seconds===false)?$now['seconds']:$seconds);
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
			case 'yearday': return ($date['yday']+1);
			case 'weekday': return $date['weekday'];
			case 'weekstart': return (self::$weekdays[$this->wkstart]+1);
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
	
	public function __clone() {
		$dateTime = new Calendar_DateTime();
		$dateTime->epoc = $this->epoc;
		return $dateTime;
	}
	
	/**
	 *	Get a date formated to a specified string-format.
	 *
	 *	@note The format used is set via the printFormat object property.
	 *
	 *	@public
	 *	@return string
	 */
	public function __toString() {
		return $this->format();
	}
	
	/**
	 *	Get a date formated to a specified string-format.
	 *
	 *	Return the object date according to a specifed format.  The format
	 *	is defined using a PHP date-format string.
	 *
	 *	@public
	 *	@param string $format The format the date to output.  (Use object property 'printFormat' if no format specfied).
	 *	@return string
	 */
	public function format($format='') {
		if ($format == '') {
			$format = $this->printFormat;
		}
		
		return date($format,$this->epoc);
	}
	
	/**
	 *	Adjust a date.
	 *
	 *	Given the objects's internally set date, adjust it by the
	 *	supplied paramaters.
	 *
	 *	@public
	 *	@param int $year The number of years to adjust it by (positive or negative).
	 *	@param int $month The number of months to adjust it by (positive or negative).
	 *	@param int $day The number of days to adjust it by (positive or negative).
	 *	@param int $hours The number of hours to adjust it by (positive or negative).
	 *	@param int $minutes The number of minutes to adjust it by (positive or negative).
	 *	@param int $seconds The number of seconds to adjust it by (positive or negative).
	 *	@return int The no. of seconds since the Unix epoc for the adjusted date.
	 */
	public function adjust($year=0,$month=0,$day=0,$hours=0,$minutes=0,$seconds=0) {
		if ((is_string($year)) && ($month != 0)) {
			switch (strtolower($year)) {
				case 'year': return $this->adjust_year($month);
				case 'month': return $this->adjust_month($month);
				case 'week': return $this->adjust_week($month);
				case 'day': return $this->adjust_day($month);
				case 'hours': return $this->adjust_hours($month);
				case 'minutes': return $this->adjust_minutes($month);
				case 'seconds': return $this->adjust_seconds($month);
				case 'weekstart': return $this->_set_week_start($month);
			}
		} else {
			$this->adjust_year($year);
			$this->adjust_month($month);
			$this->adjust_day($day);
			$this->adjust_hours($hours);
			$this->adjust_minutes($minutes);
			$this->adjust_seconds($seconds);
		}
		
	
		return $this->epoc;
	}
	
	/**
	 *	Adjust date by a supplied number of years.
	 *
	 *	@public
	 *	@param int $amount The number of years to adjust the date by.
	 *	@return int The no. of seconds since the Unix epoc for the adjusted date.
	 */
	public function adjust_year($amount) {
		$this->year += $amount;
		return $this->epoc;
	}
	
	/**
	 *	Adjust date by a supplied number of months.
	 *
	 *	@public
	 *	@param int $amount The number of months to adjust the date by.
	 *	@return int The no. of seconds since the Unix epoc for the adjusted date.
	 */
	public function adjust_month($amount) {
		$year = $this->year;
		$month = $this->month;
		$factor = $amount;
		
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
		return $this->epoc;
	}
	
	/**
	 *	Adjust date by a supplied number of weeks.
	 *
	 *	@public
	 *	@param int $amount The number of weeks to adjust the date by.
	 *	@return int The no. of seconds since the Unix epoc for the adjusted date.
	 */
	public function adjust_week($amount) {
		$this->epoc += ($amount * self::WEEK);
		return $this->epoc;
	}
	
	/**
	 *	Adjust date by a supplied number of days.
	 *
	 *	@public
	 *	@param int $amount The number of days to adjust the date by.
	 *	@return int The no. of seconds since the Unix epoc for the adjusted date.
	 */
	public function adjust_day($amount) {
		$this->epoc += ($amount * self::DAY);
		return $this->epoc;
	}
	
	/**
	 *	Adjust date by a supplied number of hours.
	 *
	 *	@public
	 *	@param int $amount The number of hours to adjust the date by.
	 *	@return int The no. of seconds since the Unix epoc for the adjusted date.
	 */
	public function adjust_hours($amount) {
		$this->epoc += ($amount * self::HOUR);
		return $this->epoc;
	}
	
	/**
	 *	Adjust date by a supplied number of minutes.
	 *
	 *	@public
	 *	@param int $amount The number of minutes to adjust the date by.
	 *	@return int The no. of seconds since the Unix epoc for the adjusted date.
	 */
	public function adjust_minutes($amount) {
		$this->epoc += ($amount * self::MINUTE);
		return $this->epoc;
	}
	
	/**
	 *	Adjust date by a supplied number of seconds.
	 *
	 *	@public
	 *	@param int $amount The number of seconds to adjust the date by.
	 *	@return int The no. of seconds since the Unix epoc for the adjusted date.
	 */
	public function adjust_seconds($amount) {
		$this->epoc += $amount;
		return $this->epoc;
	}
	
	public function set($partName,$part) {
		switch (strtolower($partName)) {
			case 'year': return $this->_set_year($part);
			case 'month': return $this->_set_month($part);
			case 'day': return $this->_set_day($part);
			case 'hours': return $this->_set_hours($part);
			case 'minutes': return $this->_set_minutes($part);
			case 'seconds': return $this->_set_seconds($part);
			case 'yearday': return $this->_set_year_day($part);
			case 'week': return $this->_set_week($part);
			case 'weekday': return $this->_set_week_day($part);
			case 'weekstart': return $this->_set_week_start($part);
		}
	}
	
	/**
	 *	Set part of a date to another value.
	 *
	 *	@private
	 *	@param int $part The amount set the part to.
	 *	@param string $partName The name of the part to change (hours|minutes|seconds|mon|mday|year).
	 *	@return The no. of seconds since the Unix epoc for the changed date.
	 */
	private function _set_part($part,$partName) {
		$date = getdate($this->epoc);
		$date[$partName] = $part;
		
		$this->epoc = mktime(
			$date['hours'],$date['minutes'],$date['seconds'],
			$date['mon'],$date['mday'],$date['year']
		);
		return $this->epoc;
	}
	
	/**
	 *	Set the year part of a date to another value.
	 *
	 *	@private
	 *	@param int $year The amount set the the year to (either 2-digit or 4 digit year).
	 *	@return The no. of seconds since the Unix epoc for the changed date.
	 */
	private function _set_year($year) {
		$year = $this->_get_four_digit_year($year);
		return $this->_set_part($year,'year');
	}
	
	/**
	 *	Set the month part of a date to another value.
	 *
	 *	@note If a negative number is supplied then it is counted backwards from the last month (eg. November is -2).
	 *
	 *	@private
	 *	@param int $month The amount set the the month to (either number, 1-12 or -1--12 or name of month, eg. Jan or January).
	 *	@return The no. of seconds since the Unix epoc for the changed date.
	 */
	private function _set_month($month) {
		$month = $this->_get_month_number($month);
		$month = (($month < 0)?(12+$month):$month);
		return $this->_set_part($month,'mon');
	}
	
	/**
	 *	Set the day part of a date to another value.
	 *
	 *	@note If a negative number is supplied then it is counted backwards from the last day of the month (eg. 29th April would be -2).
	 *
	 *	@private
	 *	@param int $day The amount set the the day to.
	 *	@return The no. of seconds since the Unix epoc for the changed date.
	 */
	private function _set_day($day) {
		$date = getdate($this->epoc);
		$month = $this->_get_month_number($date['mon']);
		$monthLength = $this->_get_month_length($date['year'],$month);
		$day = (($day < 0)?($monthLength+$day):$day);
		return $this->_set_part($day,'mday');
	}
	
	private function _get_day_no() {
		$date = getdate($this->epoc);
		$weekdayNo = ($date['wday'] + 1);
		$weekdayNo -= self::$weekdays[$this->wkstart];
		if ($weekdayNo < 1) {
			$weekdayNo += 7;
		}
		return $weekdayNo;
	}
	
	private function _get_day() {
		$date = getdate($this->epoc);
		return $date['weekday'];
	}
	
	/**
	 *	Set the hour part of a date to another value.
	 *
	 *	@note If a negative number is supplied then it is counted backwards from the end of the day (eg. 23hrs is -2).
	 *
	 *	@private
	 *	@param int $hours The amount set the the hour to.
	 *	@return The no. of seconds since the Unix epoc for the changed date.
	 */
	private function _set_hours($hours) {
		$hours = (($hours < 0)?(24+$hours):$hours);
		return $this->_set_part($hours,'hours');
	}
	
	/**
	 *	Set the minute part of a date to another value.
	 *
	 *	@note If a negative number is supplied then it is counted backwards from the end of the hour (eg. 58mins is -2).
	 *
	 *	@private
	 *	@param int $minutes The amount set the the minute to.
	 *	@return The no. of seconds since the Unix epoc for the changed date.
	 */
	private function _set_minutes($minutes) {
		$minutes = (($minutes < 0)?(60+$minutes):$minutes);
		return $this->_set_part($minutes,'minutes');
	}
	
	/**
	 *	Set the second part of a date to another value.
	 *
	 *	@note If a negative number is supplied then it is counted backwards from the end of the minute (eg. 58secs is -2).
	 *
	 *	@private
	 *	@param int $seconds The amount set the the second to.
	 *	@return The no. of seconds since the Unix epoc for the changed date.
	 */
	private function _set_seconds($seconds) {
		$seconds = (($seconds < 0)?(60+$seconds):$seconds);
		return $this->_set_part($seconds,'seconds');
	}
	
	/**
	 *	Set the year-day part of a date to another value.
	 *
	 *	@note If a negative number is supplied then it is counted backwards from the end of the year (eg. 30th Dec is -2).
	 *
	 *	@private
	 *	@param int $yearDay The amount set the the year-day to.
	 *	@return The no. of seconds since the Unix epoc for the changed date.
	 */
	private function _set_year_day($yearDay) {
		if ($yearDay < 0) {
			$daysInYear = 365;
			if ($this->_is_leap_year($this->year)) {
				$daysInYear = 366;
			}
			$yearDay += $daysInYear;
		}
		
		$this->month = 12;
		$this->day = 31;
		if ($yearDay > 0) {
			$this->year--;
		}
	
		$this->epoc += (self::DAY * $yearDay);
		
		return $this->epoc;
	}
	
	private function _get_week() {
		$yearStart = $this->_get_start_first_week();
		$week = (int) (($this->epoc - $yearStart->epoc) / (self::WEEK)) + 1;
		return $week;
	}
	
	private function _set_week_start($day) {
		if (is_int($day)) {
			if (($day > 0) && ($day < 8)) {
				$this->wkstart = self::$weekdays[($day-1)];
			}
		} else {
			$day = strtoupper(substr($day,0,2));
			if (isset(self::$weekdays[$day])) {
				$this->wkstart = $day;
			}
		}
		return (self::$weekdays[$this->wkstart]+1);
	}
	
	/**
	 *	Set the week part of a date to another value.
	 *
	 *	@note If a negative number is supplied then it is counted backwards from the end of the year (eg. week 52 is normally is -1, for 52 week year).
	 *
	 *	@private
	 *	@param int $week The amount set the the week to.
	 *	@return The no. of seconds since the Unix epoc for the changed date.
	 */
	private function _set_week($week) {
		$this->epoc -= ($this->week * self::WEEK);
		if ($week < 0) {
			$yearEnd = $this->_get_year_end();
			$weeksInYear = $yearEnd->week;
			$week = $weeksInYear+$week+1;
		}
		$this->epoc += ($week * self::WEEK);
		return $this->epoc;
	}
	
	private function _get_week_day_no($weekDay) {
		if (is_string($weekDay)){
			$weekDay = strtoupper(substr($weekDay,0,2));
		} else {
			$weekDay = self::$weekdays[$weekDay];
		}
		return $weekDay;
	}
	
	/**
	 *	Set the weekday part of a date to another value.
	 *
	 *	@note If a negative number is supplied then it is counted backwards from the end of the week (eg. Friday is -2 if the week starts on Sunday).
	 *
	 *	@private
	 *	@param int|string $weekDay The amount set the the week-day to (no. 1-7 or string in format SU, Sun or Sunday).
	 *	@return The no. of seconds since the Unix epoc for the changed date.
	 */
	private function _set_week_day($weekDay) {
		$weekDay = $this->_get_week_day_no($weekDay);
		
		$this->epoc -= (($this->dayNo * self::DAY) + self::DAY);
		for ($i = 0; $i < 7; $i++) {
			if ($this->weekDay == $weekDay) {
				break;
			}
			$this->epoc += self::DAY;
		}
			
		return $this->epoc;
	}
	
	
	/**
	 *	Get the day length of a month in a given year.
	 *
	 *	@private
	 *	@param int $year The year to use for the calcultion.
	 *	@param int|string $month The month to get the number of days in  (digit 1-12 or name, eg. Jan or January).
	 *	@return int
	 *	
	 */
	private function _get_month_length($year,$month){
		if (is_numeric($month)) {
			$month = strtolower(self::$monthName[$month]);
		} else {
			$month = substr(strtolower($month),0,3);
		}
		
		
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
	
	/**
	 *	Get the month number for a given named month.
	 *
	 *	@private
	 *	@param int|string If a number is given then that is returned, otherwise the month number is found.
	 *	@return int
	 */
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
	 *	@private
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
	
	/**
	 *	Get the 1st of January of the current year.
	 *
	 *	@note Time settings and weekstart settings are copied from the current date.
	 *
	 *	@private
	 *	@return Calendar_DateTime
	 */
	private function _get_year_start() {
		$yearStart = clone $this;
		$yearStart->month = 1;
		$yearStart->day = 1;
		return $yearStart;
	}
	
	/**
	 *	Get the 31st of December of the current year.
	 *
	 *	@note Time settings and weekstart settings are copied from the current date.
	 *
	 *	@private
	 *	@return Calendar_DateTime
	 */
	private function _get_year_end() {
		$yearEnd = clone $this;
		$yearEnd ->month = 12;
		$yearEnd ->day = 31;
		return $yearEnd ;
	}
	
	/**
	 *	Get 1st day of the 1st week in the current 'year (for the current date).
	 *
	 *	The first day might not be 1st January, if that is not the first day
	 *	of the week.  Different week staring days can change the results
	 *	of this method.  Also,  it may be in the previous year, if the current
	 *	date is part of a week, which started before 1st January.  In this case
	 *	a day from January the previous year is returned.
	 *
	 *	@note Time settings and weekstart settings are copied from the current date.
	 *
	 *	@private
	 *	@return Calendar_DateTime
	 */
	private function _get_start_first_week() {
		$yearStart = $this->_get_year_start();
		if ($yearStart->dayNo == 1) {
			return $yearStart;
		} 
		$yearStart->adjust_day((7-$yearStart->dayNo)+1);
		if ($yearStart->epoc <= $this->epoc) {
			return $yearStart;
		}
		
		$yearStart->year--;
		$yearStart->day = 1;
		if ($yearStart->dayNo == 1) {
			return $yearStart;
		}
		
		$yearStart->adjust_day((7-$yearStart->dayNo)+1);
		return $yearStart;
	}
}