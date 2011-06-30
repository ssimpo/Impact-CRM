<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *  Log Profile Clsss
 *
 *  Load a filter-profile and execute filtering according to it's settings.
 *  
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Analytics
 */
class LogProfile extends Base {
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
    public function include_line($data) {
        foreach ($this->profile as $name => $test) {
			print_r($test);
            if ((!$test['include']) && (!isset($test['value2']))) {
                if ($test['type'] == 'regx') {
                    $found = @preg_match($test['value'],$data[$test['subject']]);
					if ($found === false) {
						throw new Exception('Error in profile test: "'.$name.'"');
					} elseif ($found > 0) {
                       return false;
                    }
                }
            }
        }
        
        return true;
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
			SITE_FOLDER.CONFIG_DIRECTORY.DS.'profiles',$profile
		);
		$this->profile->open('read','settings');
    }
	
	/**
	 *	Calculate a test name from the paramters and namespace.
	 *
	 *	@private
	 *	@param array() $params The paramters to grab the name from.
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