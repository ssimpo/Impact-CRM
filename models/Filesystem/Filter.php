<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *  Log Profile Clsss
 *
 *  Load a filter-profile and execute filtering according to it's settings.
 *  
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.3
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Analytics
 */
class Filesystem_Filter extends Base {
    private $profile = array();
	private $entitySearch = array('&quot;','&amp;');
	private $entityReplace = array('"','&');
    
    /**
     *  Constructor
     *
     *  Load the requested profile and store.
     *
     *  @public
     *  @param string $type The filter-profile to load and use.
     */
    public function __construct($profile) {
        $this->_load_profile($profile);
    }
    
    /**
     *  Include the specified data (YES|NO).
     *
     *  Process the data according to the current profile and return boolean
     *  value, whether the line (which $data represents), should be included
     *  or not.
     *
     *  @public
     *  @param array() $data The processed log-line
     *  @return boolean
     */
    public function include_line(&$data) {
		foreach ($this->profile as $name => $test) {
			$subjects = explode(' ',$test['subject']);
			$value = $test['value'];
			$include = $this->_get_include($test);
			$type = $this->_get_type($value);
			$compare = $this->_get_compare($test);
			
			foreach ($subjects as $subject) {
				if (isset($test['replace'])) {
					$replace = $test['replace'];
				
					if (isset($test['replace_subject'])) {
						if ($this->_run_test($data[$subject],$value,$type,$compare) == $include) {
							$data[$test['replace_subject']] = $replace;
						}
					} else {
						$data[$subject] = preg_replace($value,$replace,$data[$subject]);
					}
				} else {
					if (isset($data[$subject])) {
						if ($this->_run_test($data[$subject],$value,$type,$compare) != $include) {
							return false;
						}
					}
				}
			}
        }
		
		return true;
    }
	
	/**
	 *	Process the supplied data according the current profile.
	 *
	 *	This will process the supplied data, doing search and replace
	 *	operations, return the new data.
	 *
	 *	@public
	 *	@param array() $data The data to process.
	 *	@return array()
	 */
	public function process($data) {
		foreach ($this->profile as $name => $test) {
			if ((isset($test['subject'])) && (isset($test['replace']))) {
				$subjects = explode(' ',$test['subject']);
				$value = $test['value'];
				$replace = $test['replace'];
				
				foreach ($subjects as $subject) {
					if (isset($data[$subject])) {
						if (isset($test['replace_subject'])) {
							$type = $this->_get_type($value);
							$compare = $this->_get_compare($test);
							$include = $this->_get_include($test);
						
							if ($this->_run_test($data[$subject],$value,$type,$compare) == $include) {
								$data[$test['replace_subject']] = $replace;
							}
						} else {
							$data[$subject] = preg_replace(
								$value,$replace,$data[$subject]
							);
						}
					}
				}

			}
			
		}
		
		return $data;
	}
	
	/**
     *  Run a test against a given subject.
     *
     *  @private
     *  @param string $subject The subject name to run the test against.
     *  @param array() $test The test to run.
     *  @param string $type The test type
     *  @param string $compare The compare to use.
     *  @return boolean Passed tested or failed?
     */
	private function _run_test($subject,$test,$type,$compare='==') {
		switch ($type) {
			case 'regx': return $this->_run_test_regx($test,$subject);
			case 'string': case 'boolean': return ($test === $subject);
			case 'date': $value = $this->_get_epoc_value($value);
			case 'int': return $this->_run_test_compare($test,$subject,$compare);
		}
		
		return false;
	}
	
	/**
     *  Run a regx-test against a given subject.
     *
     *  @private
     *  @param array() $test The test to run.
     *  @param string $compare The compare to use.
     *  @return boolean Passed tested or failed?
     */
	private function _run_test_regx($test,$subject) {
		$found = @preg_match($test,$subject);
		if ($found === false) {
			throw new Exception('Error in profile test: "'.$name.'"');
		} elseif ($found > 0) {
			return true;
		}
		
		return false;
	}
	
	/**
     *  Run a compare-test against a given subject.
     *
     *  @private
     *  @param string $subject The subject name to run the test against.
     *  @param array() $test The test to run.
     *  @param string $compare The compare to use.
     *  @return boolean Passed tested or failed?
     */
	private function _run_test_compare($test,$subject,$compare='==') {
		switch ($compare) {
			case '==': case '=': return ($test == $subject);
			case '>': return ($test > $subject);
			case '>=': case '=>': return ($test >= $subject);
			case '<': return ($test < $subject);
			case '>=': case '=>':return ($test <= $subject);
		}
	}
	
	/**
	 *	Try to figure-out a value type.
	 *
	 *	@private
	 *	@param string|int|boolean $value The value to test.
	 *	@return string The value type (string|boolean|int|regx).
	 */
	private function _get_type($value) {
		if (is_int($value)) {
			return 'int';
		} elseif (is_bool($value)) {
			return 'boolean';
		} elseif (is_string($value)) {
			$found = @preg_match('/\A\/.*\/(?:[imsxeADSUXJu]*|)/',$value);
			if ($found === false) {
				return 'string';
			} else {
				return 'regx';
			}
		}
	}
	
	/**
     *  Get the include attribute-value for a given test.
     *
     *  @private
     *  @param array() $test The test to parse
     *  @return string
     */
	private function _get_include(&$test) {
		$include = false;
		if (isset($test['include'])) {
			$include = $test['include'];
		}
		return $include;
	}
	
	/**
     *  Get the compare attribute-value for a given test.
     *
     *  @private
     *  @param array() $test The test to parse
     *  @return string
     */
	private function _get_compare($test) {
		$compare = '==';
		if (isset($test['compare'])) {
			$compare = $test['compare'];
		}
		return $compare;
	}
	
	/**
     *  Get the epoc-date for given value.
     *
     *  @private
     *  @param string|Calendar_DateTime $value The value to parse
     *  @return int
     */
	private function _get_epoc_value($value) {
		if ($value instanceof Calendar_DateTime) {
			$value = $value->epoc;
		}
		if (!is_int($subject)) {
			$dateparser = new DateParser();
			$subject = $dateparser->parse($subject);
			$subject = $subject->epoc;
		}
		
		return $value;
	}
    
    /**
     *  Load a profile into memory
     *
     *  @private
     *  @param string $profile The name of filter-profile to load.
     *  @param string $namespace Added to the profile name, used when sub-profiles are loaded to avoid name clashes.
     */
    private function _load_profile($profile,$namespace='') {
		$this->profile = new FileSystem_File(
			ROOT_BACK.MODELS_DIRECTORY.DS.'Filesystem'.DS.'Filter'.DS.'settings',
			$profile.'.xml'
		);
		$this->profile->open('read','settings');
    }
	
	/**
	 *	Calculate a test name from the parameters and namespace.
	 *
	 *	@private
	 *	@param array() $params The parameters to grab the name from.
	 *	@param string $namespace The namespace to add to the test name.
	 */
	private function _get_test_name($params,$namespace) {
		$name = trim($params['name']);
		return ((trim($namespace)=='')?$name:$namespace.'.'.$name);
	}
	
	/**
	 *	Get an array value without throwing an error if key does not exist.
	 *
	 *	@private
	 *	@param array() $array Array to get value of.
	 *	@param string $key The key name.
	 *	@return string
	 */
	private function _safe_get_array_value($array,$key) {
		return ((isset($array[$key]))?$array[$key]:'');
	}
}