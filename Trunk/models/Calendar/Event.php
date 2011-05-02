<?php
/**
 *	Calendar.Event class
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.2
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Calendar
 *	@extends Calendar_Base
 */
class Calendar_Event Extends CalendarBase implements Calendar_Object {

	function __construct() {
		parent::__construct();
	}

	public function set_start_date($date,$timezone='') {
		$DateParser = $this->factory('DateParser');
		$utc_date = $DateParser->convert_date($date,'',$timezone);
		$this->data['startDate'] = $utc_date;
	}

}