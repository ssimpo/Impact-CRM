<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *  Log Interpreter Interface
 *  
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Analytics
 */
interface LogInterpreter_Object {
   public function parse($line);
   public function rebuild_line($data);
}
?>