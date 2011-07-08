<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *	Calendar date parser class
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.4
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Calendar
 *
 *	@todo derive Factory Method from base class
 */
class DateParser Extends Base {
	private static $config=false;
	
	/**
	 *	Constructor, which loads the date detection file
	 *	
	 *	@public
	 *
	 *	@return self
	 */
	function __construct() {
		if (self::$config === false) {
			self::$config = new FileSystem_File(
				ROOT_BACK.MODELS_DIRECTORY.DS.'DateParser'.DS,
				'settings.xml'
			);
			self::$config->open('read','settings');
		}
	}
	
	/**
	 *	Convert the supplied date.
	 *
	 *	Method will apply date conversion, according to the supplied
	 *	type.  If no type is supplied then the parser tries to detect it.
	 *	
	 *	@public
	 *
	 *	@param
	 *	@return
	 */
	public function parse($date,$type='',$timezone='') {
		switch ($type) {
			case '':
				return $this->_detect($date,$timezone);
				break;
			default:
				$parser = $this->factory($type);
				return $parser->parse($date,$timezone);
		}
	}
	
	/**
	 *	Detect the date type.
	 *
	 *	Use a series of regular expressions described in a settings file
	 *	to try and detect, which format the supplied date conforms to.  Will
	 *	return the actual parsed date.
	 *	
	 *	@private
	 *
	 *	@param string $date The date-string to parse.
	 *	@param string $timezone The timezone of the supplied date.
	 *	@return date Date in PHP date format.
	 */
	protected function _detect($date,$timezone='') {
		foreach (self::$config as $name => $test) {
			if (preg_match($test['value'],$date)) {
				$parser = $this->factory($name);
				return $parser->parse($date,$timezone);
			}
		}
	}
	
	public function factory($class,$args=array()) {
		return parent::factory('DateParser_'.$class,$args);
	}
}