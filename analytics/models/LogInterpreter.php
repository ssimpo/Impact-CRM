<?php
if (!defined('DIRECT_ACCESS_CHECK')) {
	die('Direct access is not allowed');
}

/**
 *  Log Interpreter Clsss
 *
 *  This will load the interpreter for the specified log-format.
 *  
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Analytics
 */
class LogInterpreter extends Base implements LogInterpreter_Object {
    private $parser;
    
    /**
     *  Constructor
     *
     *  Load the requested interpreter, which will then be used by this class
     *  on all parsing operations.
     *
     *  @public
     *  @param string $type The log-file format being used (eg. Domino, Apache, IIS, ...etc).
     */
    public function __construct($type) {
        $this->parser = $this->factory('LogInterpreter_'.$type);
    }
    
    /**
     *  Log-line parser
     *
     *  Parse a single log-file line using the currently loaded interpreter.
     *  Results are returned to the calling code.
     *
     *  @public
     *  @param string $line One line of the log.
     *  @return array() The results of the parsing from the current interpreter.
     */
    public function parse($line) {
        return $this->parser->parse($line);
    }
}
?>