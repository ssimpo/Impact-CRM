<?php
if (!defined('DIRECT_ACCESS_CHECK')) {
	die('Direct access is not allowed');
}

/**
 *	iCal Repeat Parser Class
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.3
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Calendar
 */
class ICalRRuleParser extends Base {
	private $rrule;
	private $parser;
	
	public function __construct() {
		
	}
	
	public function parse($rrule,$start) {
		$this->rrule = $this->_split_rrule($rrule);
		$this->parser = $this->_get_parser($this->rrule);
		$this->parser->parse($this->rrule,$start);
	}
	
	/**
	 *	Get the appropriate parser for set frequency
	 *
	 *	@public
	 *	@param array()|string $rrule The iCal RRule broken into an array or just the FREQ string.
	 *	@return object Parser of type ICalRRuleParser_<FREQUENCY TYPE>
	 */
	public function factory($rrule,$args=array()) {
		if (is_string($rrule)) {
			return parent::factory('ICalRRuleParser_'.$rrule);
		} else {
			return parent::factory('ICalRRuleParser_'.$rrule['FREQ']);
		}
	}
	
	/**
	 *	Split an iCal rule into it's parts.
	 *
	 *	@private
	 *	@param string $rrule The rule to split-up.
	 *	@return array() The resulting array.
	 */
	private function _split_rrule($rrule) {
		$result = array();
		
		$parts = explode(';',strtoupper($rrule));
		foreach ($parts as $part) {
			$item = explode('=',$part);
			if (count($item) == 2) {
				$result[trim($item[0])] = $this->_get_rrule_values($part);
			} else {
				throw new Exception($rrule.' is not a valid iCal RRULE.');
			}
		}
		
		return $result;
	}
	
	/**
	 *	Grab the value(s) from an iCal RRULE part.
	 *
	 *	@private
	 *	@param string $rrule The RRULE part.
	 *	@return string|int|array() The value(s)
	 */
	private function _get_rrule_values($rrule) {
		$values = explode('=',$rrule);
		
		if (count($values) == 2) {
			$values = array_pop($values);
			
			if ($this->_contains($values,',')) {
				$values = explode(',',$values);
				for ($i = 0; $i < count($values); $i++) {
					$values[$i] = $this->_get_string_or_number($values[$i]);
				}
				return $values;
			} else {
				return $this->_get_string_or_number($values);
			}
		} else {
			throw new Exception($rrule.' is not valid as part of an iCal RRULE.');
		}
	}
	
	/**
	 *	If supplied text is numeric then return a number, otherwise a string.
	 *
	 *	@private
	 *	@param string $string The string to test and return.
	 *	@return string|int|float
	 */
	private function _get_string_or_number($string) {
		if (is_numeric($string)) {
			if ($this->_contains($string,'.')) {
				return (float) $string;
			} else {
				return (int) $string;
			}
		} else {
			return $string;
		}
		return ((is_numeric($string))?(int) $string:$string);
	}
	
	/**
	 *	Is the one snippet of text contained within another.
	 *
	 *	@private
	 *	@param String $text1 The string to search within.
	 *	@param String $text2 The string to search for.
	 *	@return Boolean
	*/
	private function _contains($text1,$text2) {
		return ((stristr($text1,$text2) !== false) ? true:false);
	}
}