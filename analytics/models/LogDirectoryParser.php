<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *  Parse a directory (or directories) for log files.
 *
 *  Look through the specified directory or directories for files, which match
 *  the test for log files.  Provide methodology for parsing through those
 *  log files one-by-one.
 *  
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Analytics
 */
class LogDirectoryParser extends Base {
    private $directories = array();
    private $logFiles = array();
    private $position = 0;
    private $dirty = false;
    
    public function __construct() {
    }
    
    /**
     *  Move the log files pointer to the start and return the first entry.
     *
     *  @public
     *  @return string The current log filename
     */
    public function start() {
        $this->_dirty_check();
        $this->position = 0;
        return $this->_get_entry();
    }
    
    /**
     *  Move the log files pointer to the end and return the last entry.
     *
     *  @public
     *  @return string The current log filename
     */
    public function end() {
        $this->_dirty_check();
        $this->position = (count($this->logFiles)-1);
        return $this->_get_entry();
    }
    
    /**
     *  Move the log files pointer forward and return an entry.
     *
     *  @public
     *  @return string The current log filename
     */
    public function next() {
        $this->_dirty_check();
        $this->position++;
        if ($this->position < count($this->logFiles)) {
            return $this->_get_entry();
        } else {
            return false;
        }
    }
    
    /**
     *  Move the log files pointer back and return an entry.
     *
     *  @public
     */
    public function previous() {
        $this->_dirty_check();
        $this->position++;
        if ($this->position >= 0) {
            return $this->_get_entry();
        } else {
            return false;
        }
    }
    
    /**
     *  Get the current entry
     *
     *  @private
     *  @return string The current log filename
     */
    private function _get_entry() {
        return $this->logFiles[$this->position];
    }
    
    /**
     *  If more directories have been added, reload the logfile names.
     */
    private function _dirty_check() {
        if ($this->dirty == true) {
            $this->_load_logfiles();
        }
    }
    
    /**
     *  Load the log file names into an array.
     *
     *  @private
     */
    public function _load_logfiles() {
        $this->logFiles = array();
        
        foreach ($this->directories as $dirname) {
            $dh = $this->_get_directory($dirname);
            while (($filename = readdir($dh)) !== false) {
                $filename = $dirname.$filename;
                if ($this->_check_is_logfile($filename)) {
                    array_push($this->logFiles,$filename);
                }
            }
        }
    }
    
    /**
     *  Add a directory to the search path
     *
     *  @note If a directory, already exists in the search path then it is
     *  moved to the top of the stack.
     *  
     *  @public
     *  @param string $dirname The directory name to add.
     */
    public function add_directory($dirname) {
        $dirname = trim($dirname);
        $keyNum = array_search($dirname,$this->directories);
        
        if ($keyNum !== false) {
            unset($this->directories[$keyNum]);
        }
        
        array_push($this->directories,$dirname);
        $this->dirty = true;
    }
    
    /**
     *  Remove a directory from the search path
     *
     *  @public
     *  @param string $dirname The directory to remove.
     */
    public function remove_directory($dirname) {
        $dirname = trim($dirname);
        $keyNum = array_search($dirname,$this->directories);
        
        if ($keyNum !== false) {
            unset($this->directories[$keyNum]);
        }
        $this->dirty = true;
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
     *  Get a directory handle.
     *
     *  @private
     *  @param string $dirname The directory name + path.
     *  @return directoryhandle|null
     */
    private function _get_directory($dirname) {
        if (is_dir($dirname)) {
            if ($dh = opendir($dirname)) {
                return $dh;
            } else {
                throw new Exception('Could not access : '.$dirname);
            }
        } else {
            throw new Exception('Directory: '.$dirname.' does not exist');
        }
        
        return null;
    }
}