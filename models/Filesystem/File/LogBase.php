<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *  Apache Log Interpreter Base Clsss
 *
 *  Most of the coding goes-on here.  Generally, the individual interpreter
 *  classes are only stubs, unless, some specific coding is needed for that
 *  log format.  Most of the functionality is loaded from the config files.
 *
 *	@todo Add caching so config is not reloaded when looping through files in a directory.
 *  
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Filesystem
 */
class Filesystem_File_LogBase extends  Filesystem_File_Text {
	protected $config;
    protected $regx_parse = array();
	private $lastLine = null;
	private $position = -1;
	protected $filters = array();
	
	/**
     *  Get a line from the open file and parse it.
     *
     *  @public
     *  @return array() The line from the open filehandle, parsed according to class rules.
     */
	public function next() {
		$line = parent::next();
		if (!is_null($line)) {
			$this->position++;
			$this->lastLine = $this->parse($line);
			return $this->lastLine;
		}
		return null;
	}

	public function rewind() {
		$this->position = 0;
		rewind($this->handle);
		$this->next();
	}
	
	public function current() {
		return $this->lastLine;
    }
	
	public function valid() {
		if (feof($this->handle)) {
			return false;
		} else {
			return true;
		}
    }
	
	public function key() {
		return $this->position;
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
		$this->regx_parse = new FileSystem_File(
			ROOT_BACK.MODELS_DIRECTORY.DS.'Filesystem'.DS.'File'.DS.'settings',
			$type.'.xml'
		);
		$this->regx_parse->open('read','settings');
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
        
        foreach ($this->regx_parse as $types => $parser) {
			$subject = $this->_get_subject($parser,$data,$line);
			
			if ((!is_null($subject)) && (isset($parser['value']))) {
				$test = $parser['value'];
				preg_match($test,$subject,$matches);
				
				$count = 1;
                $types = explode(' ',$types);
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
	
	private function _get_subject(&$parser,&$data,$line) {
		$subject = $line;
		
		if (isset($parser['subject'])) {
			$subject = trim($parser['subject']);
			if (isset($data[$subject])) {
				$subject = $data[$subject];
			} else {
				$subject = null;
			}
		}
		
		return $subject;
	}
}
?>