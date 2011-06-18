<?php
if (!defined('DIRECT_ACCESS_CHECK')) {
	die('Direct access is not allowed');
}

/**
 *	ICalRRuleParser.Base class
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Calendar
 */
abstract class ICalRRuleParser_Base Extends Base {
	protected $dateParser = null;
	private $modifiers = array(
		'INTERVAL','BYSECOND','BYMINUTE','BYHOUR',
		'BYDAY','BYMONTHDAY','BYYEARDAY','BYWEEKNO',
		'BYMONTH','BYSETPOS'
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
		$functionName = '_next_'.strtolower($rrule['MODIFIER']);
		return call_user_func(array($this,$functionName),$cdate,$rrule);
	}
	
	abstract protected function _next_interval($cdate,$rrule);
	
	protected function _next_bysecond($cdate,$rrule) {
		
	}
	
	protected function _next_byminute($cdate,$rrule) {
		
	}
	
	protected function _next_byhour($cdate,$rrule) {
		
	}
	
	protected function _next_byday($cdate,$rrule) {
		
	}
	
	protected function _next_bymonthday($cdate,$rrule) {
		
	}
	
	protected function _next_byyearday($cdate,$rrule) {
		
	}
	
	protected function _next_byweekno($cdate,$rrule) {
		
	}
	
	protected function _next_bymonth($cdate,$rrule) {
		
	}
	
	protected function _get_modifier($rrule) {
		foreach ($rrule as $rule => $values) {
			if (in_array($rule,$this->modifiers)) {
				return $rule;
			}
		}
	}
}