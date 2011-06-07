<?php
/*
*	Class for <template:include />
*	
*	@author Stephen Simpson <me@simpo.org>
*	@version 0.0.1
*	@license http://www.gnu.org/licenses/lgpl.html LGPL
*	@package Templater
*/
class Templater_Include Extends Templater_Base implements Templater_Object {
    
	public function parse($match) {
	//Load include content, according to Acl
		
		$attributes = $match['attributes'];
		$comtem = $this->component.'.xml';

		$template = '';
		if ($this->_acl($attributes)) {
			if ($attributes['type'] == 'component') {
				if ($attributes['name'] == 'main') {
					$parser = new Templater($this->application);
					$template = $parser->parse(ROOT_BACK.'/views/'.USE_TEMPLATE.'/'.$comtem);
				}
				if ($attributes['name'] == 'meta') {
					$parser = new Templater($this->application);
					$template = $parser->parse(ROOT_BACK.'/views/'.USE_TEMPLATE.'/meta/'.$comtem);
				}
			}
		}
	
		return $template;
	}
}