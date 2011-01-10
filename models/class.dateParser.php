<?php
/**
 *	Calendar date parser class
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.3
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Calendar		
 */
class dateParser {
	private static $config=false;
	
	function __construct() {
		if (!is_array($this->config)) { $this->_load_config('dateParser/settings.xml'); }
	}
	
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
	
	protected function _detect($date,$timezone='') {
		foreach ($this->config as $tester) {
			if (preg_match($tester[REGEX],$date)) {
				$parser = $this->factory($tester['CLASS']);
				return $parser->parse($date,$timezone);
			}
		}
	}
	
	protected function _load_config($path) {
		$xml = simplexml_load_file($this->_get_class_filepath().'/'.$path);
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
	
	protected function _get_class_filepath() {
		$debug = debug_backtrace();
		return dirname($debug[0][file]);
	}
	
	public static function factory($className) {
		$debug = debug_backtrace();
		$dir = dirname($debug[0][file]);
		
        if (include_once $dir.'/dateParser/class.'.str_replace('_','.',$className).'.php') {
			return new $className;
        } else {
            throw new Exception($className.' Class not found');
        }
    }
	
}