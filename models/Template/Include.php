<?php
/*
*	Class for <template:include />
*	
*	@author Stephen Simpson <me@simpo.org>
*	@version 0.0.1
*	@license http://www.gnu.org/licenses/lgpl.html LGPL
*	@package Template
*/
class Template_Include Extends Template_Base implements Template_Object {
    
	public function parse($match) {
	//Load include content, according to Acl
		
		$attributes = $match['attributes'];
		if ($this->_show($attributes)) {
			$template = $parser->parse(__DIR__.$attributes['src']);
		}
	
		return $template;
	}
}