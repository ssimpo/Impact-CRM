<?php
/*
*	Class for building HTML from a supplied template.  Template is given either as a filepath 
*	to an XML file or an XML content string. Given access to the $application object, the parser
*	can determine which page features to display to each user
*	
*	@author Stephen Simpson <me@simpo.org>
*	@version 0.1.2
*	@license http://www.gnu.org/licenses/lgpl.html LGPL
*	@package Templater
*
*	@todo Tags that open and close at same time <img /> have to be on one line this needs fixing.
*	@todo When bad dadta is passed it often results in infinite loops
*/
class Templater extends ImpactBase {
	protected $application;
	protected $component;
	protected $mainApplication;
	protected $acl;
	private $xmlstring;
	private $_standard_html_attributes = array(
		'style','class','rev','rel','href','src'
	);
	private $parser_regX = array(
		'/<template\:(block)(\b[^>]*)>((?>(?:[^<]++|<(?!\/?template\:block\b[^>]*>))+|(?R))*)<\/template\:block>/m',
		'/<template\:(loop)(\b[^>]*)>((?>(?:[^<]++|<(?!\/?template\:loop\b[^>]*>))+|(?R))*)<\/template\:loop.*?>/m',
		'/template\:(constant|variable)\[(.*?)\]/m',
		'/\<template\:(.*?) (.*?)\/>/m'
	);
	
	/**
	 *	Constructor
	 *
	 *	No constructor code is required
	 *
	 *	@public
	 */
	public function __construct() {
	}
	
	/**
	 *	Generic get property method.
	 *
	 *	Used to dynamically get a property based on live setup.
	 *
	 *	@public
	 *	@param string $property The property to get
	 */
	public function __get($property) {
		switch ($property) {
			case 'xml':
				return $this->xmlstring;
				break;
			default:
				return null;
		}
	}
	
	/**
	 *	Generic get property method.
	 *
	 *	Used to dynamically set a property based on live setup.
	 *
	 *	@public
	 *	@param string $property The property to set
	 */
	public function __set($property,$value) {
		switch ($property) {
			case 'xml':
				$this->parse($value);
				break;
			default:
				return null;
		}
	}
	
	/**
	 *	Initialization method.
	 *
	 *	Initialize the main parser setting and parse any content
	 *	supplied (optional).
	 *
	 *	@public
	 *	@param &mixed() $application The page array, containing data on the requested page (database record).
	 *	@param &mixed()/string $path If it is a string then parse as XML template or path to template.  If it is an array then it is the application array context for the template (ie. user data and other global information).
	 *	@param string $path2 Optional path/XML content to parse.
	 */
	public function init($application,$path='',$path2='') {
		
		$this->application = $this->_get_application($application);
		if (is_string($path)) {
			if ($path !='') {
				$this->parse($path);
			}
			
			$this->mainApplication = $this->application;
			$this->_set_componant($this->application);
			$this->acl = $this->application['acl'];
		} else {
			$this->mainApplication = $this->_get_application($path);
			$this->_set_componant($this->mainApplication);
			$this->acl = $this->mainApplication['acl'];
			
			if ($path2 !='') {
				$this->parse($path2);
			}
		}
	}
	
	/**
	 *	Connect the application to this class.
	 *	
	 *	@private
	 *	@param array|object The application object|array.
	 *	@return array The application settings
	 */
	private function _get_application($application) {
		if (is_object($application)) {
			return $application->settings;
		} else {
			return $application;
		}
	}
	
	/**
	 *	Set the componant from the supplied settings-array.
	 *	
	 *	@private
	 *	@param array The application settings-array.
	 */
	private function _set_componant($application) {
		if (is_object($application)) {
			return $application->settings;
		} else {
			return $application;
		}
	}
	
	/**
	 *	Parse XML template.
	 *
	 *	Parse the contents supplied or load the address supplied and parse that.
	 *
	 *	@public
	 *	@param string $path Parse the contents or if a path, load the path and parse that.
	 *	@return string The parsed content.
	 */
	public function parse($path) {
		$this->_get_xml($path);
		if ($this->xmlstring == '') {
			return '';
		}
		
		$this->xmlstring = $this->_convert_brackets_to_xml($this->xmlstring);
		
		while ($this->_contains($this->xmlstring,'<template:')) {
			foreach($this->parser_regX as $regX) {
				$this->xmlstring = preg_replace_callback(
					$regX,
					array($this, '_parse_handle'),
					$this->xmlstring
				);
			}
		}
		
		return $this->xmlstring;
	}
	
	protected function _parse_handle($matches) {
		$match = array(
			'block' => $matches[0],
			'tagname' => $matches[1],
			'attributes' => $this->_get_attributes($matches[2]),
			'content' => ((count($matches)==4)?$matches[3]:'')
		);
		$functionName = '_'.$match['tagname'];
		
		return call_user_func(array($this,$functionName),$match);
	}
	
	/**
	 *	Convert bracketed instructions to xml equivilants
	 *
	 *	The templating system allows commands to be given using double-square
	 *	brackets instead of XML.  This allows commands to issued inside a
	 *	rich-text editor without interferring with XHTML.  This method will
	 *	convert that text to XML so it can be parsed by this parser.
	 *
	 *	@example [[PLUGIN name="date"]] will become <template:plugin name="date" />
	 *	@note single quotes will be relaced with \' this is a by-product of the
	 *	PHP works and cannot be avoided.
	 *	@todo Find a work-around for single-quote escaping.
	 *
	 *	@protected
	 *	@param string $text The string to parse
	 *	@return string Converted XML-string
	 */
	protected function _convert_brackets_to_xml($text) {
		if ($this->_contains($text,'[[')) {
			$text = preg_replace(
				'/\[\[(plugin|feature) (.*?)\]\]/mie',
				'"<template:".strtolower("\1")." \2"." />"',
				$text
			);
		}
		return $text;
	}
	
	/**
	 *	Get the XML content to parse.
	 *
	 *	Grab the XML from a file or if supplied as string then grab from that. Load
	 *	XML into class XML property.
	 *
	 *	@protected
	 *	@param string $path filepath or XML string
	 */
	protected function _get_xml($path) {
		if ($path != '') {
			if (($this->_contains($path,'<')) || ($this->_contains($path,'[['))) {
				$this->xmlstring = $path;
			} else {
				@$this->xmlstring = file_get_contents($path);
			}
		} else {
			return '';
		}
	}
	
	/**
	 *	Parse <template:loop />
	 *
	 *	Loop through an array repeating enclosed XML against each
	 *	item the array.
	 *
	 *	@protected
	 *	@param array $match text containing the attributes.
	 *	@return string The parsed loop content.
	 */
	protected function _loop($match) {
		$attributes = $match['attributes'];
		$template = '';
		
		if ($this->_acl($match['attributes'])) {
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
				$parser = new Templater();
				$parser->init($item,$this->mainApplication);
				$template .= $parser->parse($match['content']);
			}
		}
		
		return $template;
	}
	
	/**
	 *	Parse <template:block />
	 *
	 *	Hide or show the content according to its ACL attributes.  Tag offers
	 *	a good way to restrict content to particular users or to display data
	 *	on particular days/times.
	 *
	 *	@protected
	 *	@param array $match text containing the attributes.
	 *	@return string The block parsing results.
	 */
	protected function _block($match) {
		if ($this->_acl($match['attributes'])) {
			return $match['content'];
		} else {
			return '';
		}
	}
	
	/**
	 *	Parse <template:data />
	 *
	 *	Include some data either from the supplied application settings,
	 *	supplied array, or supplied attributes.  Include against ACL.
	 *
	 *	@protected
	 *	@param array $match text containing the attributes.
	 *	@return string The data results.
	 */
	protected function _data($match) {
		$attributes = $match['attributes'];
		
		$template = '';
		if ($this->_acl($attributes)) {
			if (array_key_exists('name',$attributes)) {
				$template = $this->_get_application_item($attributes['name']);
			} elseif (array_key_exists('opentag',$attributes)) {
				$htmlAttributes = '';
				foreach($this->_standard_html_attributes as $attr) {
					if (array_key_exists($attr,$attributes)) {
						$htmlAttributes .= ' '.$attr.'="'.$attributes[$attr].'"';
					}
				}
				$template = '<'.$attributes['opentag'].$htmlAttributes.'>';
			} elseif (array_key_exists('closetag',$attributes)) {
				$template = '</'.$attributes['closetag'].'>';
			}
		}
		
		//Here you need to parse the content for more template data (allows for plugins...etc)
		if (array_key_exists('parsedata',$attributes)) {
			if ($attributes['parsedata'] = 'true') {
				$parser = new Templater($this->application);
				$template = $parser->parse($template);
			}
		}

		return $template;
	}
	
	protected function _include($match) {
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
	
	protected function _feature($match) {
	//Load a HTML snippet
		$attributes = $match['attributes'];
		$template = '';
		
		if ($this->_acl($attributes)) { //Can be defined directly or in the database
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
	
	protected function _feature_loader($ids,$showone) {
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
					
				if ($this->_acl($feature)) { //If defined in database - double-lock system
					$parser = new Templater($this->application);
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
	
	/**
	 *	Parse <template:plugin />
	 *
	 *	Run a specified plugin with the supplied attributes.
	 *
	 *	@protected
	 *	@param array $matches text containing the attributes.
	 *	@return string The plugin results.
	 */
	protected function _plugin($match) {
		$attributes = $match['attributes'];
		$template = '';
		
		if ((array_key_exists('name',$attributes)) && ($this->_acl($attributes))) {
			$plugin = Plugin::factory($attributes['name']);
			if ($plugin !== false) {
				$template = $plugin->run($attributes);
			}
		}
		
		return $template;
	}
	
	protected function _notblank($testers) {
	//Are any of the variables blank?
		$testers = explode(',',$testers);
		foreach ($testers as $test) {
			$test = trim($test);
			if (array_key_exists($test,$this->application)) {
				if (empty($this->application[$test])) {
					return false;
				}
			} elseif (array_key_exists($test,$this->mainApplication)) {
				if (empty($this->mainApplication[$test])) {
					return false;
				}
			} else {
				return false;
			}
		}
		return true;
	}
	
	protected function _acl($attributes) {
	//Handle the Acl - Loose meaning of Acl as it includes access-rights according to language,
	// media-type and date/time as well as user-roles
	
		//Restrictions based on a value not being blank/null/zero
		if (array_key_exists('notblank',$attributes)) {
			if (!$this->_notblank($attributes['notblank'])) {
				return false;
			}
		}
		
		//Restrictions based on media - eg. [PC],[FACEBOOK],[MOBILE] ...etc
		if (array_key_exists('media',$attributes)) {
			$test = I::reformat_role_string($attributes['media']);
			if (!$this->_contains($test,'['.$this->application['media'].']')) {
				return false;
			}
		}
		
		//Restrictions based on language - eg. en_gb, es, de, jp ...etc
		if (array_key_exists('lang',$attributes)) {
			$test = I::reformat_role_string($attributes['lang']);
			if (!$this->_contains($test,'['.$this->applications['lang'].']')) {
				return false;
			}
		}
		
		//Restriction based on dates/times
		if ( 
			(array_key_exists('start',$attributes)) || (array_key_exists('end',$attributes)) || 
			(array_key_exists('duration',$attributes)) ||
			(array_key_exists('exdate',$attributes)) || (array_key_exists('exrule',$attributes)) ||
			(array_key_exists('rdate',$attributes)) || (array_key_exists('rrule',$attributes)) ||
			(array_key_exists('ical',$attributes)) 
		) {
			if (!$this->_ical($attributes)) {
				return false;
			}
		}
		
		//Main Acl functionality based on user groups and special on-the-fly groups
		// eg. [WEB],[ADMIN],[CRMGROUP:5],[GEOTOWN:Middlesbrough],[FBEVENT_INVITED:12578975] ...etc
		if (!array_key_exists('include',$attributes)) {
			$attributes['include']='';
		}
		if (!array_key_exists('exclude',$attributes)) {
			$attributes['exclude']='';
		}
		
		return $this->acl->allowed($attributes['include'],$attributes['exclude']);
	}
	
	protected function _ical(&$attributes) {
		
		$ical = 'BEGIN:VEVENT'."\n";
		if (array_key_exists('start',$attributes)) {
			$ical .= 'DTSTART:'.$attributes['start']."\n";
		}
		if (array_key_exists('end',$attributes)) {
			$ical .= 'DTEND:'.$attributes['end']."\n";
		}
		if (array_key_exists('duration',$attributes)) {
			$ical .= 'DURATION:'.$attributes['duration']."\n";
		}
		if (array_key_exists('exdate',$attributes)) {
			$ical .= 'EXDATE:'.$attributes['exdate']."\n";
		}
		if (array_key_exists('exrule',$attributes)) {
			$ical .= 'EXRULE:'.$attributes['exrule']."\n";
		}
		if (array_key_exists('rdate',$attributes)) {
			$ical .= 'RDATE:'.$attributes['rdate']."\n";
		}
		if (array_key_exists('rrule',$attributes)) {
			$ical .= 'RRULE:'.$attributes['rrule']."\n";
		}
		$ical .= 'END:VEVENT';
		
		$iParser = new iCalParser($ical);
		
		return $iParser->isLive();
		
	}
	
	protected function _variable(&$matches) {
		return $this->_get_application_item($matches[2]);
	}
	
	protected function _constant(&$matches) {
		return constant($matches[2]);
	}
	
	/**
	 *	Get attributes from text string.
	 *
	 *	Parse a string and return the attribute values contained in it. Parser
	 *	assumes the attributes are written like: att1="val1" att2="val" ...etc.
	 *	Results are returned as an array in format (att1=>val1,att2=>val2).
	 *
	 *	@protected
	 *	@param string $att The XML snippet containing the attributes to be parsed.
	 *	@return string() Array of attributes stored as key/value pairs.
	 */
	protected function _get_attributes($att) {
		$attributes = array();
		
		if (!empty($att)) {
			$count = preg_match_all('/([a-zA-Z0-9_]+)[= ]+[\"\'](.*?)[\"\']/',$att,$matches);
			if ($count !== false) {
				for ($i = 0; $i < $count; $i++) {
					$attributes[$matches[1][$i]] = $matches[2][$i];
				}
			}
		}
	
		return $attributes;
	}
	
	/**
	 *	Reformat a date string.
	 *
	 *	Internal method to reformat a date string from yyyy-mm-dd hh:mm:ss
	 *	to yyyymmddThhmmss format.
	 *
	 *	@protected
	 *	@param string $date Date string to reformat.
	 *	@return string Reformatted string.
	 */
	protected function _date_reformat($date) {
		return str_replace(':','',str_replace('-','',str_replace(' ','T',$date))).'Z';
	}
	
	/**
	 *	Test for text snippet in another string.
	 *
	 *	@protected
	 *	@param string $txt1 The string to search.
	 *	@param string $txt2 The string to search for.
	 *	@return boolean Was the text found?
	 */
	protected function _contains($txt1,$txt2) {
		$pos = stripos($txt1, $txt2);
		return ($pos !== false) ? true:false;
	}
	
	/**
	 *	Test whether two strings are the same after trimming and case matching.
	 *
	 *	@private
	 *	@param string $text1 The first item to compare.
	 *	@param string $text2 The second item to compare.
	 *	@return boolean
	 */
	private function _is_equal($text1,$text2) {
		return (strtolower(trim($text1)) == strtolower(trim($text2)));
	}
	
	/**
	 *	Get an application array item.
	 *
	 *	Searches in $this->application and $this->mainApplication for the
	 *	specified key and returns its value if it exists.  If nothing is found
	 *	return a blank string.
	 *	
	 *	@protected
	 *	@param string $key The key to search for.
	 *	@return string
	 */
	protected function _get_application_item($key) {
		if (array_key_exists($key,$this->application)) {
			return $this->application[$key];
		} elseif (array_key_exists($key,$this->mainApplication)) {
			return $this->mainApplication[$key];
		}
		
		return '';
	}
}

?>