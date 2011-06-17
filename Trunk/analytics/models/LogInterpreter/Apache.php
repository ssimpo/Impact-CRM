<?php
if (!defined('DIRECT_ACCESS_CHECK')) {
	die('Direct access is not allowed');
}

/**
 *  Apache Log Interpreter Clsss
 *  
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Analytics
 */
class LogInterpreter_Apache extends LogInterpreter_Base implements LogInterpreter_Object {
    
    /**
     *  Constructor
     *
     *  Load the Apache log-format options.
     */
    public function __construct() {
        $this->_load_config('Apache');
    }
    
    /**
     *  Parse a single log-line.
     *
     *  Will break the line down into it's various components and return an
     *  array with results. Calls the base-class equivalent method and then,
     *  does it's own specific processing.
     *
     *  @public
     *  @param string $line One line from the log.
     *  @return array() The results of the parsing.
     */
    public function parse($line) {
        $parsed = parent::parse($line);
        
        return $parsed;
    }
    
     /**
     *  Rebuild a logline from the parsed data array
     *
     *  @public
     *  @param array() $data The parsed logline as an array.
     *  @return string The reconstructed logline
     */
    public function rebuild_line($data) {
        
    }
}
?>