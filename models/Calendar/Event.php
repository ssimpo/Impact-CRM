<?php
/**
 *	Calendar.Event class
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.3
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Calendar
 *	@extends Calendar_Base
 */
class Calendar_Event Extends CalendarBase implements Calendar_Object {

	function __construct() {
		parent::__construct();
	}
	
	public function set_date($name, $date, $timezone='') {
		$DateParser = $this->factory('DateParser');
		$utc_date = $DateParser->convert_date($date,'',$timezone);
		$this->data[$name] = $utc_date;
	}

	public function set_start_date($date,$timezone='') {
		$this->set_date('startDate',$date,$timezone);
	}
	
	public function set_end_date($date,$timezone='') {
		$this->set_date('dateStamp',$date,$timezone);
	}
	
	public function set_date_stamp($date,$timezone='') {
		$this->set_date('endDate',$date,$timezone);
	}
	
	public function set_created_date($date,$timezone='') {
		$this->set_date('createdDate',$date,$timezone);
	}
	
	public function set_last_modified_date($date,$timezone='') {
		$this->set_date('lastModifiedDate',$date,$timezone);
	}

}