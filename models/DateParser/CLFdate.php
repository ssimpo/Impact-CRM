<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *	Calendar.Event class
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.3
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Calendar	
 */
class DateParser_CLFdate implements DateParser_Object {
	
	/**
	 *	Date parser method.
	 *
	 *	@param string $date The date to parse.
	 *	@param string $timezone The timezone of the date.
	 *	@return date The date in standard PHP date format.
	 */
	public function parse($date,$timezone='') {
		$datetime = new Calendar_DateTime();
		
		$hour=$minute=$second=0;$year=$day=$month='';
		$datetime->year = substr($date,7,4);
		$datetime->month = substr($date,3,3);
		$datetime->day = substr($date,0,2);
		$datetime->hours = substr($date,12,2);
		$datetime->minutes = substr($date,15,2);
		$datetime->seconds = substr($date,18,2);
		
		$tzHours = (int) substr($date,21,3);
		$tzMinutes = (int) substr($date,21,1).substr($date,24,2);
		
		$datetime->adjust_hours($tzHours);
		$datetime->adjust_minutes($tzMinutes);
		
		return $datetime;
	}
}