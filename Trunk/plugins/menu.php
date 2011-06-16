<?php
if (!defined('DIRECT_ACCESS_CHECK')) {
	die('Direct access is not allowed');
}

/**
 *	Menu Plugin
 *
 *	Plugin to display HTML menus.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Plugin
 */
class Plugin_Menu implements Impact_Plugin {
	private $_database;
	private $_startLevel = 1;
	private $_endLevel = 2;
	private $_attributes = '';
	private $_standard_html_attributes = array(
		'style','class','rev','rel','href','src'
	);
	
	/**
	 *	Return a HTML menu according to the supplied attributes
	 *
	 *	@public
	 *	@return string The XML for the menu.
	 */
	public function run($attributes) {
	    $this->_init($attributes);
	    $menu = $this->_get_menu();
	    $template = $this->_build_template();
	    
	    $parser = new Template();
	    $parser->init($menu,Application::instance());
		
	    return $parser->parse($template);
	}
	
	/**
	 *	Initialization of data.
	 *
	 *	@private
	 *	@param array $attributes The XML attriutes supplied (in format key1=value1,...etc).
	 */
	private function _init($attributes) {
	    $this->_attributes = $attributes;
	    $this->_database = Database::instance();
	    if (array_key_exists('startLevel',$attributes)) {
			$this->_startLevel = (int) $attributes['startLevel'];
	    }
	    if (array_key_exists('startendLevel',$attributes)) {
			$this->_startLevel = (int) $attributes['endLevel'];
	    }
	}
	
	/**
	 *	Get the menu array object for set menu.
	 *
	 *	@private
	 *	@return array The menu array.
	 */
	private function _get_menu() {
	    $menu = array('children'=>array());
	    
	    if (array_key_exists('menu',$this->_attributes)) {
			$menu['children'] = $this->_database->get_menu(
				$this->_attributes['menu'],
				$this->_startLevel,
				$this->_endLevel
			);
	    }
	    
	    return $menu;
	}
	
	/**
	 *	Build and return an Impact template for the menu
	 *
	 *	@private
	 *	@return string The menu template ready for parsing.
	 */
	private function _build_template() {
	    $template = '';
	    
	    for ($i = $this->_startLevel; $i <= $this->_endLevel; $i++) {
			if (array_key_exists('menuBlock',$this->_attributes)) {
				$tagAttributes = '';
				foreach($this->_standard_html_attributes as $attr) {
					if (array_key_exists('menu'.$attr,$this->_attributes)) {
						$tagAttributes .= ' '.$attr.'="'.$this->_attributes['menu'.$attr].'"';
					}
				}
				$template .= '<template:data notblank="children" opentag="'.$this->_attributes['menuBlock'].'"'.$tagAttributes.' />';
			}
			
			$template .= '<template:loop name="children">';
			if (array_key_exists('itemBlock',$this->_attributes)) {
				$tagAttributes = '';
				foreach($this->_standard_html_attributes as $attr) {
					if (array_key_exists('item'.$attr,$this->_attributes)) {
						$tagAttributes .= ' '.$attr.'="'.$this->_attributes['item'.$attr].'"';
					}
				}
				$template .= '<'.$this->_attributes['itemBlock'].$tagAttributes.'>';
				$template .= '<a href="template:variable[path]"><template:data name="title" /></a>';
				$template .= '</'.$this->_attributes['itemBlock'].'>';
			} else {
				$template .= '<a href="template:variable[path]"><template:data name="title" /></a>';
			}
	    }
	    for ($i = $this->_startLevel; $i <= $this->_endLevel; $i++) {
			$template .= '</template:loop>';
			if (array_key_exists('menuBlock',$this->_attributes)) {
				$template .= '<template:data notblank="children" closetag="'.$this->_attributes['menuBlock'].'" />';
			}
	    }
	    
	    return $template;
	}
	
}
?>