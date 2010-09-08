<?php
/**
*		Calendar.Event class
*		
*		@Author: Stephen Simpson
*		@Version: 0.0.1
*		@License: LGPL
*		
*/

class Calendar_Event Extends Calendar_Supercass {

	function __construct() {
		parent::__construct();
	}

	public function set_startDate($date,$timezone='') {
		$dateParser = $this->factory('dateParser');
		$utc_date = $dateParser->convert_date($date,'',$timezone);
		$this->data[startDate] = $utc_date;
	}

}