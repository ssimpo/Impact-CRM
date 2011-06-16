<?php
if (!defined('DIRECT_ACCESS_CHECK')) {
	die('Direct access is not allowed');
}

/**
 *	Calendar.Timezone class
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.4
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Calendar
 *	@extends Calendar_Base
 */
class Calendar_Timezone Extends Calendar_Base implements Calendar_Object {

    public function __construct() {
		parent::__construct();
		$this->data['standard'] = array();
		$this->data['daylight'] = array();
    }
	
	public function set_tzid($id) {
		$this->data['tzid'] = $id;
		$this->calendar->set_timezone($this,$id,$this->get_cal_id());
	}
    
    /**
     *	Create a timezone block (Standard|Daylight).
     *
     *	@public
     *	@param string $type The block identity-type (should be STANDARD or DAYLIGHT).
     *	@return array Reference to the timezone block.
     */
    public function create_block($type) {
		$block = $this->factory('Calendar_Timezone_Block');
		$block->calendar = $this->calendar;
	
		$count = array_push(
			$this->data[strtolower($type)],
			$block
		);
	
		return $block;
    }
}