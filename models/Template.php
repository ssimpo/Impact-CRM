<?php
/*
*	Class for building HTML from a supplied template.  Template is given either as a filepath 
*	to an XML file or an XML content string. Given access to the $application object, the parser
*	can determine which page features to display to each user
*	
*	@author Stephen Simpson <me@simpo.org>
*	@version 0.1.3
*	@license http://www.gnu.org/licenses/lgpl.html LGPL
*	@package Template
*
*	@todo Tags that open and close at same time <img /> have to be on one line this needs fixing.
*	@todo When bad dadta is passed it often results in infinite loops
*/
class Template extends ImpactBase {
	protected $application;
	protected $mainApplication;
	protected $acl;
	
	private $xmlstring;
	private $parser_regX = array(
		'/<template\:(loop)(\b[^>]*)>((?>(?:[^<]++|<(?!\/?template\:loop\b[^>]*>))+|(?R))*)<\/template\:\1.*?>/m',
		'/<template\:(block)(\b[^>]*)>((?>(?:[^<]++|<(?!\/?template\:block\b[^>]*>))+|(?R))*)<\/template\:\1>/m',
		'/<template\:(.*?)(\b[^>]*)>((?>(?:[^<]++|<(?!\/?template\:block\b[^>]*>))+|(?R))*)<\/template\:\1>/m',
		'/template\:(constant|variable)\[(.*?)\]/m',
		'/\<template\:(.*?) (.*?)\/>/m'
	);
	private $parsers = array();
	
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
			$this->acl = $this->application['acl'];
		} else {
			$this->mainApplication = $this->_get_application($path);
			$this->acl = $this->mainApplication['acl'];
			
			if ($path2 !='') {
				$this->parse($path2);
			}
		}
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
		
		while (I::contains($this->xmlstring,'<template:')) {
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
	
	private function _parse_handle($matches) {
		$match = $this->_create_match_array($matches);
		$className = 'Template_'.$match['tagname'];
		$parser = $this->_get_parser($className);
		
		return $parser->parse($match);
	}
	
	private function _create_match_array($matches) {
		$match = array(
			'block' => $matches[0],
			'tagname' => $matches[1],
			'attributes' => $this->_get_attributes($matches[2]),
			'content' => ((count($matches)==4)?$matches[3]:'')
		);
		
		if ((empty($match['attributes'])) && (empty($match['content'])) && (!empty($matches[2]))) {
			$match['content'] = $matches[2];
		}
		
		return $match;
	}
	
	private function _get_parser($className) {
		if (!array_key_exists($className,$this->parsers)) {
			$this->parsers[$className] = $this->factory($className);
			$this->parsers[$className]->init($this->application,$this->mainApplication);
		}
		
		return $this->parsers[$className];
	}
	
	/**
	 *	Get the XML content to parse.
	 *
	 *	Grab the XML from a file or if supplied as string then grab from that. Load
	 *	XML into class XML property.
	 *
	 *	@private
	 *	@param string $path filepath or XML string
	 */
	private function _get_xml($path) {
		if ($path != '') {
			if ((I::contains($path,'<')) || (I::contains($path,'[['))) {
				$this->xmlstring = $path;
			} else {
				@$this->xmlstring = file_get_contents($path);
			}
		} else {
			return '';
		}
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
	 *	@private
	 *	@param string $text The string to parse
	 *	@return string Converted XML-string
	 */
	private function _convert_brackets_to_xml($text) {
		if (I::contains($text,'[[')) {
			$text = preg_replace(
				'/\[\[(plugin|feature) (.*?)\]\]/mie',
				'"<template:".strtolower("\1")." \2"." />"',
				$text
			);
		}
		return $text;
	}
	
	/**
	 *	Get attributes from text string.
	 *
	 *	Parse a string and return the attribute values contained in it. Parser
	 *	assumes the attributes are written like: att1="val1" att2="val" ...etc.
	 *	Results are returned as an array in format (att1=>val1,att2=>val2).
	 *
	 *	@private
	 *	@param string $att The XML snippet containing the attributes to be parsed.
	 *	@return string() Array of attributes stored as key/value pairs.
	 */
	private function _get_attributes($att) {
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
	 *	Get an application array item.
	 *
	 *	Searches in $this->application and $this->mainApplication for the
	 *	specified key and returns its value if it exists.  If nothing is found
	 *	return a blank string.
	 *	
	 *	@private
	 *	@param string $key The key to search for.
	 *	@return string
	 */
	private function _get_application_item($key) {
		if (array_key_exists($key,$this->application)) {
			return $this->application[$key];
		} elseif (array_key_exists($key,$this->mainApplication)) {
			return $this->mainApplication[$key];
		}
		
		return '';
	}
}

?>