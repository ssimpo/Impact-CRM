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
		$comtem = $this->component.'.xml';

		$template = '';
		if ($this->_show($attributes)) {
			if ($attributes['type'] == 'component') {
				if ($attributes['name'] == 'main') {
					$parser = new Template($this->application);
					$template = $parser->parse(ROOT_BACK.'/views/'.USE_TEMPLATE.'/'.$comtem);
				}
				if ($attributes['name'] == 'meta') {
					$parser = new Template($this->application);
					$template = $parser->parse(ROOT_BACK.'/views/'.USE_TEMPLATE.'/meta/'.$comtem);
				}
			}
		}
	
		return $template;
	}
}