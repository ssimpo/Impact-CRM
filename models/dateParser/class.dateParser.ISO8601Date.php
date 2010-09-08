<?php
/**
*		Calendar.Event class
*		
*		@Author: Stephen Simpson
*		@Version: 0.0.1
*		@License: LGPL
*		
*/

class dateParser_ISO8601Date {
	
	public function parse($date,$timezone='') {
		$utc = false;
		$dateLen = strlen($date);
		
		$hour=$minute=$second=0;$year=$day=$month='';
		if (($dateLen == 8) || ($dateLen == 15) || ($dateLen == 16)) {
			$year = substr($date,0,4);
			$month = substr($date,4,2);
			$day = substr($date,6,2);
			if (($dateLen == 15) || ($dateLen == 16)) {
				$hour = substr($date,9,2);
				$minute = substr($date,11,2);
				$second = substr($date,13,2);
			}
			
			if ($dateLen == 16) {//Not sure if this is right way round + no timezone parsing yet!
				$utc = gmmktime($hour,$minute,$second,$month,$day,$year);
			} else {
				$utc = mktime($hour,$minute,$second,$month,$day,$year);
			}
		}
		
		return $utc;
	}
}