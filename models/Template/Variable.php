<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *	Class for template:variable
 *	
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Template
 */
class Template_Variable Extends Template_Base implements Template_Object {
    
	public function parse($match) {
		return $this->_get_application_item($match['content']);
	}
}