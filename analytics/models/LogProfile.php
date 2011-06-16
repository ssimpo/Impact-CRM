<?php
if (!defined('DIRECT_ACCESS_CHECK')) {
	die('Direct access is not allowed');
}

/**
 *  Log Profile Clsss
 *
 *  Load a filter-profile and excute filtering according to it's settings.
 *  
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Analytics
 */
class LogProfile extends Base {
    private $profile = array();
    
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
     *  Include the specfied data (YES|NO).
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
        foreach ($this->profile as $test) {
            if (!$test['include']) {
                if ($test['type'] == 'regx') {
                    $found = preg_match($test['value'],$data[$test['subject']]);
                    if ($found > 0) {
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
     */
    private function _load_profile($profile) {
        $profileFilename = SITE_FOLDER.CONFIG_DIRECTORY.DS.'profiles'.DS.$profile.'.xml';
        $params = $this->_load_xml_parameters($profileFilename);
        $data = array();
        
        foreach ($params as $param) {
            $data[trim($param['name'])] = $this->_process_paramter($param);
        }
            
        $this->profile = $data;
    }
    
    /**
     *  Process a set of paramters into the internal format.
     *
     *  @private
     *  @param array() $params The parameters to parse.
     *  @return array() The processed paramters.
     */
    private function _process_paramter($params) {    
        return array(
            'name' => trim($params['value']),
            'include' => ((trim($params['include'])=='yes')?true:false),
            'type' => trim($params['type']),
            'value' => str_replace('&quot;','"',$params['value']),
            'subject' => trim($params['subject'])
        );
    }
    
    /**
     *  Load the paramters from a specfied XML file.
     *
     *  @private
     *  @param string $filename The name of the XML file to load.
     *  @return array() The paramters or blank array.
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