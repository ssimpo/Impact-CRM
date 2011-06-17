<?php
if (!defined('DIRECT_ACCESS_CHECK')) {
	die('Direct access is not allowed');
}

/**
 *  Lotus/IBM Domino Log Interpreter Clsss
 *  
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Analytics
 */
class LogInterpreter_Domino extends LogInterpreter_Base implements LogInterpreter_Object {
    
    /**
     *  Constructor
     *
     *  Load the Domino log-format options.
     */
    public function __construct() {
        $this->_load_config('Domino');
    }
    
    /**
     *  Parse a single log-line.
     *
     *  Will break the line down into it's various componants and return an
     *  array with results. Calls the base-class equvilant method and then,
     *  does it's own specfic processing.
     *
     *  @public
     *  @param string $line One line from the log.
     *  @return array() The results of the parsing.
     */
    public function parse($line) {
        $parsed = parent::parse($line);
        $parsed['domino_id'] = strtoupper($parsed['domino_id']);
        
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
        return $data['ip'].' '.$data['domain'].' - ['
        .$data['date'].':'.$data['time'].' '.$data['timezone'].'] "'
        .$data['method'].' '.$data['request'].' '.$data['protocol']
        .'" '.$data['status'].' '.$data['size'].' "'
        .$data['referer'].'" "'.$data['agent']
        .'" '.$data['processing_time'].' "'.$data['cookie'].'" "'
        .$data['server_path'].'"';
        
    }
}
?>