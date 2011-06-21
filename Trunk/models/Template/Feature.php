<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *	Class for <template:feature />
 *	
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Template
 */
class Template_Feature Extends Template_Base implements Template_Object {
    
	public function parse($match) {
	//Load a HTML snippet
		$attributes = $match['attributes'];
		$template = '';
		
		if ($this->_show($attributes)) { //Can be defined directly or in the database
			$showone = false;
			if (array_key_exists('showone',$attributes)) {
				$showone = ($this->_is_equal($attributes['showone'],'true') ? true:false);
			}
			
			if (array_key_exists('name',$attributes)) {
				$template = $this->_feature_loader($attributes['name'],$showone);
			}
			if ($this->_is_equal($template,'')) {
				if (array_key_exists('default',$attributes)) {
					$template = $this->_feature_loader($attributes['default'],$showone);
				}
			}
			
		}
		
		return $template;
	}
	
	private function _feature_loader($ids,$showone) {
	//Load the HTML snippets from the database
		
		$ids = explode(',',$ids);
		$feature = '';
		foreach ($ids as $id) {
			$id = trim($id);
					
			if (is_numeric($id)) {
				$feature = db_record('SELECT * FROM features WHERE ID='.$id);
			} else {
				$feature = db_record('SELECT * FROM features WHERE name="'.$id.'"');
			}
				
			if ($feature) {
				$feature['start'] = $this->_date_reformat($feature['start']);
				$feature['end'] = $this->_date_reformat($feature['end']);
					
				if ($this->_show($feature)) { //If defined in database - double-lock system
					$parser = new Template($this->application);
					$template .= $parser->parse(stripslashes($feature['HTML']));
				}
			}
			
			if ($showone) {
				if (!$this->_is_equal($template,'')) {
					break;
				}
			}
		}	
		
		return $template;
	}
}