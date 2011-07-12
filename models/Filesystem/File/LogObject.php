<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *  Log Interpreter Interface
 *  
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Filesystem
 */
interface Filesystem_File_LogObject {
   public function parse($line);
   public function rebuild_line($data);
}
?>