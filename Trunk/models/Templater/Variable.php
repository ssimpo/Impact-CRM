<?php
/*
*	Class for template:variable
*	
*	@author Stephen Simpson <me@simpo.org>
*	@version 0.0.1
*	@license http://www.gnu.org/licenses/lgpl.html LGPL
*	@package Templater
*/
class Templater_Variable Extends Templater_Base implements Templater_Object {
    
	public function parse($matches) {
		return $this->_get_application_item($matches[2]);
	}
}