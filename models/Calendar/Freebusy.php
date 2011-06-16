<?php
if (!defined('DIRECT_ACCESS_CHECK')) {
	die('Direct access is not allowed');
}

/**
 *	Calendar.Freebusy class
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.4
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Calendar
 *	@extends Calendar_Base
 */
class Calendar_Freebusy Extends Calendar_Base implements Calendar_Object {
	
    public function __construct() {
		parent::__construct();
    }
	
	public function set_uid($id) {
		$this->data['uid'] = $id;
		$this->calendar->set_id_lookup($this,$id);
	}
	
}