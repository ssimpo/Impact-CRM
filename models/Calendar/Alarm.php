<?php
if (!defined('DIRECT_ACCESS_CHECK')) {
	die('Direct access is not allowed');
}

/**
 *	Calendar.Alarm class
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.4
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Calendar
 *	@extends Calendar_Base
 */
class Calendar_Alarm Extends Calendar_Base implements Calendar_Object {
	
	public function __construct() {
		parent::__construct();
	}

}