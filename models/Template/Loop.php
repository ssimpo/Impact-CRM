<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *	Class for <template:loop />
 *	
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Template
 */
class Template_Loop Extends Template_Base implements Template_Object {
    
	/**
	 *	Parse <template:loop />
	 *
	 *	Loop through an array repeating enclosed XML against each
	 *	item the array.
	 *
	 *	@public
	 *	@param array $match text containing the attributes.
	 *	@return string The parsed loop content.
	 */
	public function parse($match) {
		$attributes = $match['attributes'];
		$template = '';
		
		if ($this->_show($match['attributes'])) {
			$array = '';
			if (array_key_exists('name',$attributes)) {
				$array = $this->_get_application_item($attributes['name']);
				if ($array == '') {
					return '';
				}
			} else {
				$array = $this->application;
			}
			
			foreach ($array as $item) {
				$parser = new Template();
				$parser->init($item,$this->mainApplication);
				$template .= $parser->parse($match['content']);
			}
		}
		
		return $template;
	}

}