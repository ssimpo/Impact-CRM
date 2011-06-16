<?php
if (!defined('DIRECT_ACCESS_CHECK')) {
	die('Direct access is not allowed');
}

/**
 *  Log Parser Clsss
 *
 *  Parse the specified set of logs, according to the specified profile, using
 *  the interpreter class for the log-format used.
 *  
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Analytics
 */
class LogParser extends Base {
    private $interpreter;
    private $profiles = array();
    
    public function __construct($type) {
        $this->interpreter = new LogInterpreter('Domino');
    }
    
    /**
     *  Parse log files in specified directory.
     *
     *  Will parse every logfile (according to the internal rules set) within
     *  the specified directory.  Parsing will be implemented using the
     *  specified profile filter.
     *
     *  @public
     *  @param string $dir The directory to parse logs from.
     *  @param string $profile The name of the filter profile to use.
     */
    public function parse($dir,$profile) {
        $this->_get_profile($profile);
        $this->_parse_directory($dir,$profile);
    }
    
    /**
     *  Load a filter profile into memory.
     *
     *  @private
     *  @param string $profile Name of profile to load.
     *  @return array() The loaded profile (also stored in private class property).
     */
    private function _get_profile($profile) {
        if (!array_key_exists($profile,$this->profiles)) {
            $this->_load_profile($profile);
        }
        return $this->profiles[$profile];
    }
    
    /**
     *  Load a profile into memory
     *
     *  @private
     *  @param string $profile The name of filter-profile to load.
     */
    private function _load_profile($profile) {
        $profileFilename = SITE_FOLDER.CONFIG_DIRECTORY.DS.'profiles'.DS.$profile.'.xml';
        
        if (is_file($profileFilename)) {
            $config = simplexml_load_file($profileFilename);
            $data = array();
        
            foreach ($config->param as $param) {
                $data[trim($param['name'])] = array(
                    'name' => trim($param['value']),
                    'include' => ((trim($param['include'])=='yes')?true:false),
                    'type' => trim($param['type']),
                    'value' => str_replace('&quot;','"',$param['value']),
                    'subject' => trim($param['subject'])
                );
            }
            
            $this->profiles[$profile] = $data;
        } else {
            throw new Exception('Profile: "'.$profile.'" does not exist');
        }
    }
    
    /**
     *  Parse an entire directory
     *
     *  Use the specified profile against each logfile in a directory.
     *
     *  @private
     *  @param string $dir The name of the directory to to process.
     *  @param string $profile The name of filter-profile to use.
     */
    private function _parse_directory($dir,$profile) {
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($filename = readdir($dh)) !== false) {
                    if ($this->_check_is_logfile($filename)) {
                        $this->_load_file($dir.$filename,$profile);
                    }
                }
            } else {
                throw new Exception('Could not access : '.$dir);
            }
        } else {
            throw new Exception('Directory: '.$dir.' does not exist');
        }
    }
    
    /**
     *  Is the specified file a log file?
     *
     *  @private
     *  @param string $filename The name of the file to check.
     *  @return boolean
     */
    private function _check_is_logfile($filename) {
        $found = preg_match('/\.log/i',$filename);
        return (($found==1)?true:false);
    }
    
    /**
     *  Load a log file and process it according to the specified profile.
     *
     *  @private
     *  @param string $filename The name of the file to tom process.
     *  @param string $profile The name of filter-profile to use.
     */
    private function _load_file($filename,$profile) {
        $handle = @fopen($filename,'r');
        if ($handle) {
            while (!feof($handle)) {
                $line = $this->_get_line($handle);
                $this->_parse_line($line,$profile);
            }
            fclose($handle);
        } else {
            return false;
        }
    }
    
    /**
     *  Get a line from the log file.
     *
     *  @private
     *  @param filehandle $handle The filehandle to get a line from.
     *  @return string The line from specified handle.
     */
    private function _get_line($handle) {
        if ($handle) {
            if (!feof($handle)) {
                return fgets($handle);
            }
        }
        
        return null;
    }
    
    /**
     *  Parse a single line
     *
     *  Use the specified profile against each the supplied log line.
     *
     *  @private
     *  @param string $line The log-line to parse.
     *  @param string $profile The name of filter-profile to use.
     */
    private function _parse_line($line,$profile) {
        $parsed = $this->interpreter->parse($line);
        
        $include = true;
        foreach ($this->profiles[$profile] as $test) {
            if (!$test['include']) {
                if ($test['type'] == 'regx') {
                    $found = preg_match(
                        $test['value'],
                        $parsed[$test['subject']]
                    );
                    if ($found > 0) {
                       $include = false; 
                    }
                }
            }
        }
        
        return $include;
    }
    
    
    
}
?>