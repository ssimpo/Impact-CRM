<?php
/*
*	Class for template:constant
*	
*	@author Stephen Simpson <me@simpo.org>
*	@version 0.0.1
*	@license http://www.gnu.org/licenses/lgpl.html LGPL
*	@package Templater
*/
class Templater_Constant Extends Templater_Base implements Templater_Object {
    
	public function parse($match) {
		return constant($match['attributes']);
	}
	
}