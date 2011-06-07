<?php
/**
 *	The interface for Templater objects
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Templater
 */
interface Templater_Object {
   public function parse($matches);
   public function init($application,$mainApplication);
}
?>