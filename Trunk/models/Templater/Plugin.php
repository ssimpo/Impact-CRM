<?php
/*
*	Class for <template:plugin />
*	
*	@author Stephen Simpson <me@simpo.org>
*	@version 0.0.1
*	@license http://www.gnu.org/licenses/lgpl.html LGPL
*	@package Templater
*/
class Templater_Plugin Extends Templater_Base implements Templater_Object {
    
	/**
	 *	Parse <template:plugin />
	 *
	 *	Run a specified plugin with the supplied attributes.
	 *
	 *	@public
	 *	@param array $matches text containing the attributes.
	 *	@return string The plugin results.
	 */
	public function parse($match) {
		$attributes = $match['attributes'];
		$template = '';
		
		if ((array_key_exists('name',$attributes)) && ($this->_show($attributes))) {
			$plugin = Plugin::factory($attributes['name']);
			if ($plugin !== false) {
				$template = $plugin->run($attributes);
			}
		}
		
		return $template;
	}
}