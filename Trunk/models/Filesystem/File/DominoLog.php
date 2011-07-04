<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *  Lotus/IBM Domino Log Interpreter Clsss
 *  
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.2
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Analytics
 */
class Filesystem_File_DominoLog extends Filesystem_File_LogBase implements Filesystem_File_LogObject {
    private $dateparser;
    
    /**
     *  Constructor
     *
     *  Load the Domino log-format options.
     */
    public function __construct() {
        $this->_load_config('DominoLog');
        $this->dateparser = new DateParser();
        $this->position = 0;
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
        
        $agent = '';
        if ($parsed['agent']) {
            try {
                $agent = get_browser($parsed['agent'],true);
            } catch (Exception $e) {
                $browscap = new Browscap(ROOT_BACK.'database'.DS);
                $agent = $browscap->getBrowser($parsed['agent'],true);
            }
        
            foreach ($agent as $key => $value) {
                $parsed['agent_'.strtolower($key)] = $value;
            }
        }
        
        $parsed['datetime'] = $this->dateparser->parse($parsed['datetime']);
        
        return $parsed;
    }
    
    public function write($data) {
        $ldata = $data;
        if (is_array($data)) {
            parent::write($this->rebuild_line($ldata));
        } else {
            parent::write($ldata);
        }
        
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
        .$data['server_path'].'"'."\n";
        
    }
}
?>