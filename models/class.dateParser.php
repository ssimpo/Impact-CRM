<?php
/**
 *	Calendar date parser class
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.3
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Calendar
 *
 *	@todo derive Factory Method from base class
 */
class dateParser Extends Impact_Base {
	private static $config=false;
	
	/**
	 *	Constructor, which loads the date detection file
	 *	
	 *	@public
	 *
	 *	@return self
	 */
	function __construct() {
		if (!is_array($this->config)) {
			$this->_load_config('dateParser/settings.xml');
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
	public function convert_date($date,$type='',$timezone='') {
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
		foreach ($this->config as $tester) {
			if (preg_match($tester[REGEX],$date)) {
				$parser = $this->factory($tester['CLASS']);
				return $parser->parse($date,$timezone);
			}
		}
	}
	
	/**
	 *	Load the date detection, settings file.
	 *	
	 *	@private
	 *
	 *	@param string $path Path to the settings file.
	 */
	protected function _load_config($path) {
		$xml = simplexml_load_file($this->_get_include_directory().'/'.$path);
		$this->config = array();
		
		foreach ($xml->param as $param) {
			switch ($param['type']) {
				case 'regx':
					$value = (string) $param['value'];
					$class = (string) 'dateParser_'.$param['name'];
					array_push($this->config,array('REGEX'=>$value,'CLASS'=>$class));
					break;
			}
		}
	}
	
	/**
	 *	Factory method for classes, which are part of the date parser.
	 *
	 *	@static
	 *	@public
	 *
	 *	@param	$className The name of the class to create.
	 *	@return	object	The requested class if it was found.
	 */
	public static function factory($className) {
		$dir = $this._get_include_directory();
		
		if (include_once $dir.'/dateParser/class.'.str_replace('_','.',$className).'.php') {
			return new $className;
		} else {
			throw new Exception($className.' Class not found');
		}
	}
	
}