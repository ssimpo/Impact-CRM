<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *  Apache Log Interpreter Base Clsss
 *
 *  Most of the coding goes-on here.  Generally, the individual interpreter
 *  classes are only stubs, unless, some specific coding is needed for that
 *  log format.  Most of the functionality is loaded from the config files.
 *  
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Analytics
 */
class Filesystem_File_LogBase extends  Filesystem_File_Text {
    protected $regx_parse = array();
    protected $regx_parse2 = array();
	
	/**
     *  Get a line from the open file and parse it.
     *
     *  @public
     *  @return array() The line from the open filehandle, parsed according to class rules.
     */
	public function next() {
		$line = parent::next();
		if (!is_null($line)) {
			return $this->parse($line);
		}
	}
	
	public function all() {
		throw new Exception('Cannot load all for this type of file, use the next() method.');
	}
	
    /**
     *  Load the log config document for the specified class.
     *
     *  @protected
     *  @param string $type The log-format type (eg. Domino, Apache, IIS, ...etc).
     */
    protected function _load_config($type) {
        $config = simplexml_load_file(ROOT_BACK.MODELS_DIRECTORY.DS.'Filesystem'.DS.'File'.DS.'settings'.DS.$type.'.xml');
        
		foreach ($config->param as $param) {
            switch ($param['type']) {
                case 'string': case 'integer': case 'boolean':
                    break;
                case 'regx':
                    $value = str_replace('&quot;','"',$param['value']);
                    if (!isset($param['subject'])) {
                        $this->regx_parse[trim($param['name'])] = $value;
                    } else {
                        $this->regx_parse2[trim($param['name'])] = array(
                            'subject' => $param['subject'],
                            'regx' => $value
                        );
                    }
                    break;
            }
        }
    }
    
    /**
     *  Parse a single log-line.
     *
     *  Will break the line down into it's various components and return an
     *  array with results.
     *
     *  @public
     *  @param string $line One line from the log.
     *  @return array() The results of the parsing.
     */
    public function parse($line) {
        $data = array();
        
        $parsers = array($this->regx_parse,$this->regx_parse2);
        
        foreach ($parsers as $parser) {
            foreach ($parser as $types => $test) {
                $subject = (is_array($test)? $data[trim($test['subject'])]:$line);
                $test = (is_array($test)? $test['regx']:$test);
                preg_match($test,$subject,$matches);
                
                $count = 1;
                $types = explode(',',$types);
                foreach ($types as $type) {
                    if (!empty($matches)) {
                        $data[$type] = $matches[$count];
                    } else {
                        $data[$type] = null;
                    }
                    $count++;
                }
            }
        }
        
        return $data;
    }
}
?>