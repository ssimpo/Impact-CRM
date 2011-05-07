<?php
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
	
	public function set_tzid($value) {
		$this->set_id(md5($value));
		$this->data['tzid'] = $value;
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
	
		$count = array_push(
			$this->data[strtolower($type)],
			$block
		);
	
		return $block;
    }
}