<?php
/**
 *	The interface for Template objects
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Template
 */
interface Template_Object {
   public function parse($matches);
   public function init($application,$mainApplication);
}
?>