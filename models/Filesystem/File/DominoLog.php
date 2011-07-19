<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *  Lotus/IBM Domino Log Interpreter Clsss
 *  
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.3
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Filesystem
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
        if (isset($parsed['domino_id'])) {
            $parsed['domino_id'] = strtoupper($parsed['domino_id']);
        }
       
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
        if (isset($parsed['cookie'])) {
            $parsed['cookie_text'] = $parsed['cookie'];
            $parsed['cookie'] = $this->_parse_cookie($parsed['cookie']);
        }
        
        $parsed['datetime'] = $this->dateparser->parse($parsed['datetime']);
        
        return $parsed;
    }
    
    private function _parse_cookie($cookiesText) {
        if (trim($cookiesText) == '') {
            return array();
        }
        $parts = $this->_get_cookie_parts($cookiesText);
        $cookies = $this->_get_named_cookies($parts);
        foreach ($cookies as $name => $value) {
            $cookies[$name] = $this->_parse_cookie_values($value);
        }
        
        return $cookies;
    }
    
    private function _parse_cookie_values($cookie) {
        $parts = preg_split('/\&amp;|\&/',urldecode($cookie));
        if (count($parts) == 1) {
            return $cookie;
        }
    
        $values = array();
        foreach ($parts as $part) {
            $found = preg_match('/\A(.*?)=(.*)\Z/',$part,$matches);
            if ($found == 1) {
                $values[trim($matches[1])] = $matches[2];
            } else {
                $found = preg_match('/\A[A-Za-z0-9]+\Z/',$part,$matches);
                if ($found == 1) {
                    $values[$part] = null;
                } else {
                    array_push($values,$part);
                }
            }
        }
        
        return I::array_trim($values);
    }
    
    private function _get_named_cookies($parts) {
        $cookies = array();
        
        foreach ($parts as $part) {
            $found = preg_match('/\A(.*?)=(.*)\Z/',$part,$matches);
            if ($found == 1) {
                $cookies[trim($matches[1])] = $matches[2];
            } else {
                $found = preg_match('/\A[A-Za-z0-9]+\Z/',$part,$matches);
                if ($found == 1) {
                    $cookies[$part] = null;
                } else {
                    array_push($cookies,$part);
                }
            }
        }
        
        return I::array_trim($cookies);
    }
    
    private function _get_cookie_parts($cookieText) {
        $parts = explode(';',$cookieText);
        $parts2 = array();
        
        foreach ($parts as $part) {
            $parts2 = array_merge(explode('|',$part),$parts2);
        }
        
        return I::array_trim($parts2);
    }
    
    public function write($data,$filter='') {
        if ($filter != '') {
            if (!isset($this->filters[$filter])) {
                $this->filters[$filter] = new Filesystem_Filter($filter);
            }
            $filter = $this->filters[$filter];
            if (!$filter->include_line($data)) {
                return false;
            }
        }
        if (is_array($data)) {
            parent::write($this->rebuild_line($data));
        } else {
            parent::write($data);
        }
        return true;
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
        .'" '.$data['processing_time'].' "'.$data['cookie_text'].'" "'
        .$data['server_path'].'"'."\n";
        
    }
}
?>