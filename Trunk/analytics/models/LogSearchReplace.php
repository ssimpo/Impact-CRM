<?php
if (!defined('DIRECT_ACCESS_CHECK')) {
	die('Direct access is not allowed');
}

/**
 *  Log Search & Replace Clsss
 *
 *  Seach and replace on a log line according to the loaded profile
 *  
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Analytics
 */
class LogSearchReplace extends Base {
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
     *  Do a search and replace on the parsed logline.
     *
     *  @public
     *  @param array() $data The parsed logline to process
     *  @return array() The processed data.
     */
    public function parse_line($data) {
        foreach ($this->profile as $name => $test) {
			$subjects = explode(',',$test['subject']);
			
			foreach ($subjects as $subject) {
				$newitem = @preg_replace(
					$test['value'],$test['replace'],$data[$subject]
				);
				if (trim($newitem) !== null) {
					$data[$subject] = $newitem;
				}
			}
			
        }
        
        return $data;
    }
    
    /**
     *  Load a profile into memory
     *
     *  @private
     *  @param string $profile The name of filter-profile to load.
     *  @param string $namespace Added to the profile name, used when sub-profiles are loaded to avoid name clashes.
     */
    private function _load_profile($profile,$namespace='') {
        $profileFilename = SITE_FOLDER.CONFIG_DIRECTORY.DS.'profiles'.DS.$profile.'.xml';
        $params = $this->_load_xml_parameters($profileFilename);

        foreach ($params as $param) {
			$name = $this->_get_test_name($param,$namespace);
            $this->profile[$name] = $this->_process_paramter($param);
			if ($this->profile[$name]['type'] == 'profile') {
				$this->_load_profile($this->profile[$name]['value'],$name);
				unset($this->profile[$name]);
			}
        }
    }
    
    /**
     *  Process a set of parameters into the internal format.
     *
     *  @private
     *  @param array() $params The parameters to parse.
     *  @return array() The processed parameters.
     */
    private function _process_paramter($params) {
        return array(
            'type' => trim($this->_safe_get_array_value($params,'type')),
            'value' => str_replace(
				$this->entitySearch,$this->entityReplace,
				trim($this->_safe_get_array_value($params,'value'))
			),
			'replace' => str_replace(
				$this->entitySearch,
				$this->entityReplace,
				trim($this->_safe_get_array_value($params,'replace'))
			),
			'subject' => trim($this->_safe_get_array_value($params,'subject')
			),
        );
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
    
    /**
     *  Load the parameters from a specified XML file.
     *
     *  @private
     *  @param string $filename The name of the XML file to load.
     *  @return array() The parameters or blank array.
     */
    private function _load_xml_parameters($filename) {
        if (is_file($filename)) {
            $config = simplexml_load_file($filename);
            return $config->param;
        } else {
            return array();
        }
    }
}