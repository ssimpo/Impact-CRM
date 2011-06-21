<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *	The interface for ICalRRuleParser objects
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Calendar
 */
interface ICalRRuleParser_Object {
	public function parse($rrule);
}
?>