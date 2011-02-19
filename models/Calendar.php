<?php
/**
*	Calendar class
*		
*	@author Stephen Simpson <me@simpo.org>
*	@version 0.0.4
*	@license http://www.gnu.org/licenses/lgpl.html LGPL
*	@package Calendar
*	@todo Start using a generic factory class.
*/
include_once 'dateParser/interface.dateParserObject.php';

class Calendar Extends ImpactBase {
	protected $objects = array();
	
	/**
	 *	Method factory.
	 *
	 *	@public
	 *	@param string $name The name of the method called (eg. add_event)
	 *	@param array $arguments The arguments passed to the method.
	 *	@return object Calendar class of specified type
	 */
	public function __call($name,$arguments) {
		$parts = explode('_',strtolower($name));
		if (count($parts) == 2) {
			switch ($parts[0]) {
				case 'add':
					$id = $this->_rnd_string();
					$this->objects[$id] = $this->factory('Calendar_'.$parts[1]);
					$this->objects[$id]->set_id($id);
					return $this->objects[$id];
					break;
			}
		}
	}
	
	/**
	 *	Create a random generic string (UNID).
	 *
	 *	@protected
	 *	@return string*32 Random, 32-digit hexadecimal string
	 */
	protected function _rnd_string() {
		return md5(chr(rand(1,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)));
	}
}

/**
 *      The interface for Calendar objects
 *
 *      Calendar objects interface, eg. Events, Journal entries, Todo items,
 *      ...etc. 
 *      
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.2
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Calendar
 */
interface Calendar_Object {
   
}

class CalendarBase {
	protected $data = array();

	function __construct() {
		$this->data['repeatIncludeRules'] = array();
		$this->data['repeatExcludeRules'] = array();
		$this->data['repeatInclude'] = array();
		$this->data['repeatExclude'] = array();
		$this->data['startDate'] = '';
		$this->data['endDate'] = '';
		$this->data['duration'] = 0;
	}

	public function __call($name,$arguments) {
		$parts = explode('_',$name);
		if (count($parts) == 2) {
			switch ($parts[0]) {
				case 'set':
					if (count($arguments)>0) {
						$this->data[$parts[1]] = $arguments[0];
						return true;
					} else {
						return false;
					}
					break;
				case 'get':
					if (array_key_exists($parts[1],$this->data)) {
						return $this->data[$parts[1]];
					} else {
						print_r($this->data);
						return false;
					}
			}
		}
	}

	public function expand_repeats($start,$end) {
		$Repeat_Parser = $this->factory('Repeat_Parser');
		if ($Repeat_Parser) {
			$Repeat_Parser->set_start_date($this->data['startDate']);
			$Repeat_Parser->set_end_date($this->data['endDate']);
			$Repeat_Parser->set_duration($this->data['duration']);
			$Repeat_Parser->set_repeat_include_rules($this->data['repeatIncludeRules']);
			$Repeat_Parser->set_repeat_exclude_rules($this->data['repeatExcludeRules']);
			$Repeat_Parser->set_repeat_include($this->data['repeatInclude']);
			$Repeat_Parser->set_repeat_exclude($this->data['repeatExclude']);
			
			$dates = $Repeat_Parser->expand($start,$end);
			
			return $dates;
		} else {
			return $false;
		}
	}

}