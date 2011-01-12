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
class Calendar_Event Extends Calendar_Base {

	function __construct() {
		parent::__construct();
	}

	public function set_startDate($date,$timezone='') {
		$dateParser = $this->factory('dateParser');
		$utc_date = $dateParser->convert_date($date,'',$timezone);
		$this->data[startDate] = $utc_date;
	}

}