<?php
if (!defined('DIRECT_ACCESS_CHECK')) {
	die('Direct access is not allowed');
}

/**
 *	Class for FREQ=SECONDLY
 *	
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.3
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Calendar
 */
class ICalRRuleParser_Secondly Extends ICalRRuleParser_Base implements ICalRRuleParser_Object {
	
	
	/**
	 *	Parse an iCal RRULE
	 *	
	 *	@public
	 *	@param array() $rrule The iCal RRULE broken-down into it's componants.
	 *	@param DateTime $start The iCal DTSTART for the RRULE.
	 *	@return
	 */
	public function parse($rrule) {
		if (array_key_exists('COUNT',$rrule)) {
			$this->_parse_count($rrule);
		} elseif (array_key_exists('UNTIL',$rrule)) {
			$this->_parse_until($rrule);
		} else {
			throw new Exception('Array does not contain a correctly formatted iCal RRULE.');
		}
	}
	
	private function _parse_count($rrule) {
		$dates = array($rrule['DTSTART']);
		$cdate = $rrule['DTSTART'];
		for ($i = 1; $i < $rrule['COUNT']; $i++) {
			$cdate += $rrule['INTERVAL'];
			array_push($dates,$cdate);
		}
	}
	
	private function _parse_until($rrule) {
		$dates = array($rrule['DTSTART']);
		$rrule['RREND'] = $this->_get_date($rrule['UNTIL']);
		
		$cdate = $rrule['DTSTART'];
		while ($cdate < $rrule['RREND']) {
			$cdate = $this->_get_next($cdate,$rrule);
			array_push($dates,$cdate);
		}
	}

	
}