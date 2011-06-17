<?php
if (!defined('DIRECT_ACCESS_CHECK')) {
	die('Direct access is not allowed');
}

/**
 *	Class for template:constant
 *	
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Template
 */
class Template_Constant Extends Template_Base implements Template_Object {
    
	public function parse($match) {
		return constant($match['content']);
	}
	
}