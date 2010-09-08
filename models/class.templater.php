<?php
/*
		Class for building HTML from a supplied template.  Template is given either as a filepath 
		to an XML file or an XML content string. Given access to the $application object, the parser
		can determine which page featuers to display to each user
		
		@author Stephen Simpson <ssimpo@gmail.com>
		@copyright Styephen Simpson, <ssimpo@gmail.com>
		@license http://www.gnu.org/licenses/gpl.html GNU GPL
		@Version: 0.2.0
		
*/

class templater {

	protected $xml = '';
	protected $application;
	protected $component;
	protected $mainApplication;
	protected $ACL;

	function __construct() {
	}
	
	public function init(&$application,&$path='',$path2='') {
	#If XML or path to XML is passed, call the parser as well, otherwise just create the object
		
		$this->application = array();
		$this->mainApplication = array();
		
		$this->application = $application;
		if (is_string($path)) {
			if ($path !='') { $this->parse($path); }
			$this->component = $this->application[component];
			$this->ACL = $this->application[ACL];
		} else {
			$this->mainApplication = $path;
			$this->component = $this->mainApplication[component];
			$this->ACL = $this->mainApplication[ACL];
			#$this->application[ACL] = $this->mainApplication[ACL];
			if ($path2 !='') { $this->parse($path2); }
		}
	}
	
	public function parse($path) {
	#Core of the parser
	
		$this->_getXML($path);
		
		if ($this->_contains(&$this->xml,'[[')) {
			$this->xml = preg_replace(
				'/\[\[(plugin|feature) (.*?)\]\]/mie',
				'"<template:".strtolower("\1")." \2"." />"',
				&$this->xml
			);
		}
		
		while ($this->_contains(&$this->xml,'<template:block')) {
			$this->xml = preg_replace_callback(
				'/<template\:block(\b[^>]*)>((?>(?:[^<]++|<(?!\/?template\:block\b[^>]*>))+|(?R))*)<\/template\:block>/m',
				array(&$this, '_block'),
				&$this->xml
			);
		}
		
		while ($this->_contains(&$this->xml,'<template:loop')) {
			$this->xml = preg_replace_callback(
				'/<template\:loop(\b[^>]*)>((?>(?:[^<]++|<(?!\/?template\:loop\b[^>]*>))+|(?R))*)<\/template\:loop>/m',
				array(&$this, '_loop'),
				&$this->xml
			);
		}
		
		$this->xml = preg_replace_callback(
			'/template\:(constant|variable)\[(.*?)\]/m',
			array(&$this, '_variable'),
			&$this->xml
		);
		
		$this->xml = preg_replace_callback(
			'/\<template\:(.*?) (.*?)\/>/m',
			array(&$this, '_template'),
			&$this->xml
		);
		
		return $this->xml;
	}
	
	protected function _getXML($path) {
	#Grab the XMl from a file or if supplied as XMLString then grab from that
		
		if ($path != '') {
			#if (($this->_contains($path,'<')) || ($this->_contains($path,'[[')) || ($this->_contains($path,':'))) {
			if (($this->_contains($path,'<')) || ($this->_contains($path,'[['))) {
				$this->xml = $path;
			} else {
				@$this->xml = file_get_contents($path);
			}
		} else {
			return '';
		}
	}
	
	protected function _loop(&$matches) {
	#Allows looping through an array, repeating the inner block against each item
		$attributes = $this->_getAttributes($matches[1]);
		$template = '';
		
		if ($this->_ACL($attributes)) {
			$array = '';
			if (array_key_exists('name',$attributes)) {
				if (array_key_exists($attributes[name],$this->application)) {
					$array =  $attributes[name];
				} elseif (array_key_exists($attributes[name],$this->mainApplication)) {
					$array =  $attributes[name];
				} else {
					return '';
				}
			} else {
				$array = $this->application;
			}
			
			foreach ($array as $item) {
				$parser = new templater($item,$this->mainApplication);
				$template .= $parser->parse($matches[2]);
			}
		}
		
		return $template;
	}
	
	protected function _block(&$matches) {
	#If the ACL allows then include block, otherwise return a blank
	
		$attributes = $this->_getAttributes($matches[1]);
		
		if ($this->_ACL($attributes)) {
			return $matches[2];
		} else {
			return '';
		}
	}
	
	protected function _template(&$matches) {
	#Match data/include tags and deal with them accordingly
		
		switch ($matches[1]) {
			case 'data':
				return $this->_data($matches[2]);
				break;
			case 'include':
				return $this->_include($matches[2]);
				break;
			case 'feature':
				return $this->_feature($matches[2]);
				break;
			case 'plugin':
				return $this->_plugin($matches[2]);
				break;
		}
	}
	
	protected function _data($match) {
	#Include data content, according to ACL
	
		$attributes = $this->_getAttributes($match);
	
		$template = '';
		if ($this->_ACL($attributes)) {
			if (array_key_exists($attributes[name],$this->application)) {
				$template =  $this->application[$attributes[name]];
			} elseif (array_key_exists($attributes[name],$this->mainApplication)) {
				$template =  $this->mainApplication[$attributes[name]];
			} else {
				$template =  '';
			}
		}
		
		#Here you need to parse the content for more template data (allows for plugins...etc)
		if (array_key_exists('parsedata',$attributes)) {
			if ($attributes[parsedata] = 'true') {
				$parser = new templater($this->application);
				$template = $parser->parse($template);
			}
		}
	
		return $template;
	}
	
	protected function _include($match) {
	#Load include content, accoring to ACL
		
		$attributes = $this->_getAttributes($match);
		$comtem = $this->component.'.xml';

		$template = '';
		if ($this->_ACL($attributes)) {
			if ($attributes[type] == 'component') {
				if ($attributes[name] == 'main') {
					$parser = new templater($this->application);
					$template = $parser->parse(ROOT_BACK.'/views/'.USE_TEMPLATE.'/'.$comtem);
				}
				if ($attributes[name] == 'meta') {
					$parser = new templater($this->application);
					$template = $parser->parse(ROOT_BACK.'/views/'.USE_TEMPLATE.'/meta/'.$comtem);
				}
			}
		}
	
		return $template;
	}
	
	protected function _feature($match) {
	#Load a HTML snippet
		$attributes = $this->_getAttributes($match);
		$template = '';
		
		if ($this->_ACL($attributes)) {#Can be defined directly or in the database
			$showone = false;
			if (array_key_exists('showone',$attributes)) {
				$showone = (isEqual($attributes[showone],'true') ? true:false);
			}
			
			if (array_key_exists('name',$attributes)) {
				$template = $this->_feature_loader($attributes[name],$showone);
			}
			if (isEqual($template,'')) {
				if (array_key_exists('default',$attributes)) {
					$template = $this->_feature_loader($attributes['default'],$showone);
				}
			}
			
		}
		
		return $template;
	}
	
	protected function _feature_loader($ids,$showone) {
	#Load the HTML snippets from the database
		
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
				$feature[start] = $this->_dateReformat($feature[start]);
				$feature[end] = $this->_dateReformat($feature[end]);
					
				if ($this->_ACL($feature)) {#If defined in database - double-lock system
					$parser = new templater($this->application);
					$template .= $parser->parse(stripslashes($feature[HTML]));
				}
			}
			
			if ($showone) {
				if (!isEqual($template,'')) { break; }
			}
		}	
		
		return $template;
	}
	
	protected function _plugin($match) {
	#Load a plugin
		$attributes = $this->_getAttributes($match);
		$template = '';
		
		if ((array_key_exists('name',$attributes)) && ($this->_ACL($attributes))) {
			$plugin = Plugin::factory($attributes[name]);
			if ($plugin !== false) {
				$template = $plugin->run($attributes);
			}
		}
		
		return $template;
	}
	
	protected function _notblank ($testers) {
	#Are any of the variables blank?
		$testers = explode(',',$testers);
		foreach ($testers as $test) {
			$test = trim($test);
			if (array_key_exists($test,$this->application)) {
				if ($this->application[$test] == '') {
					return false;
				} elseif (is_numeric($this->application[$test])) {
					if ($this->application[$test] == 0) { return false; }
				}
			} elseif (array_key_exists($test,$this->mainApplication)) {
				if ($this->mainApplication[$test] == '') {
					return false;
				} elseif (is_numeric($this->mainApplication[$test])) {
					if ($this->mainApplication[$test] == 0) { return false; }
				}
			} else {
				return false;
			}
		}
		return true;
	}
	
	protected function _ACL (&$attributes) {
	#Handle the ACL - Loose meaning of ACL as it includes access-rights according to language,
	# media-type and date/time as well as user-roles
		
		#Restrictions based on a value not being blank/null/zero
		if (array_key_exists('notblank',$attributes)) {
			if (!$this->_notblank($attributes[notblank])) { return false; }
		}
		
		#Restrictions based on media - eg. [PC],[FACEBOOK],[MOBILE] ...etc
		if (array_key_exists('media',$attributes)) {
			$test = $this->_testFormatter($attributes[media]);
			if (!$this->_contains($test,'['.$this->application[media].']')) { return false; }
		}
		
		#Restrictions based on language - eg. en_gb, es, de, jp ...etc
		if (array_key_exists('lang',$attributes)) {
			$test = $this->_testFormatter($attributes[lang]);
			if (!$this->_contains($test,'['.$this->applications[lang].']')) { return false; }
		}
		
		#Restriction based on dates/times
		if ( 
			(array_key_exists('start',$attributes)) || (array_key_exists('end',$attributes)) || 
			(array_key_exists('duration',$attributes)) ||
			(array_key_exists('exdate',$attributes)) || (array_key_exists('exrule',$attributes)) ||
			(array_key_exists('rdate',$attributes)) || (array_key_exists('rrule',$attributes)) ||
			(array_key_exists('ical',$attributes)) 
		) {
			if (!$this->_ical($attributes)) { return false; }
		}
		
		#Main ACL functionality based on user groups and special on-the-fly groups
		# eg. [WEB],[ADMIN],[CRMGROUP:5],[GEOTOWN:Middlesbrough],[FBEVENT_INVITED:12578975] ...etc
		if (!array_key_exists('include',$attributes)) { $attributes['include']=''; }
		if (!array_key_exists('exclude',$attributes)) { $attributes['exclude']=''; }
		
		
		return $this->ACL->allowed($attributes['include'],$attributes['exclude']);
	}
	
	protected function _ical(&$attributes) {
		
		$ical = 'BEGIN:VEVENT'."\n";
		if (array_key_exists('start',$attributes)) { $ical .= 'DTSTART:'.$attributes[start]."\n"; }
		if (array_key_exists('end',$attributes)) { $ical .= 'DTEND:'.$attributes[end]."\n"; }
		if (array_key_exists('duration',$attributes)) { $ical .= 'DURATION:'.$attributes[duration]."\n"; }
		if (array_key_exists('exdate',$attributes)) { $ical .= 'EXDATE:'.$attributes[exdate]."\n"; }
		if (array_key_exists('exrule',$attributes)) { $ical .= 'EXRULE:'.$attributes[exrule]."\n"; }
		if (array_key_exists('rdate',$attributes)) { $ical .= 'RDATE:'.$attributes[rdate]."\n"; }
		if (array_key_exists('rrule',$attributes)) { $ical .= 'RRULE:'.$attributes[rrule]."\n"; }
		$ical .= 'END:VEVENT';
		
		$iParser = new iCalParser($ical);
		
		return $iParser->isLive();
		
	}
	
	protected function _variable(&$matches) {
		switch ($matches[1]) {
			case 'variable':
				if (array_key_exists($matches[2],$this->application)) {
					return $this->application[$matches[2]];
				} elseif (array_key_exists($matches[2],$this->mainApplication)) {
					return $this->mainApplication[$matches[2]];
				} else {
					return '';
				}
				break;
			case 'constant':
				return constant($matches[2]);
				break;
		}
	}
	
	protected function _testFormatter ($test) {
	
		$test = '['.str_replace(',','],[',$test).']';
		$test = str_replace('[[','[',$test);
		$test = str_replace(']]',']',$test);
	
		return $test;
	}

	protected function _getAttributes ($att) {
	#Return an array of attributes from supplied text-string

		$attributes = array();
		$count = preg_match_all('/([a-zA-Z0-9_]+)[= ]+[\"\'](.*?)[\"\']/',$att,$matches);
		for ($i = 0; $i <= $count; $i++) {$attributes[$matches[1][$i]] = $matches[2][$i];}
	
		return $attributes;
	}
	
	protected function _dateReformat($date) {
		return str_replace(':','',str_replace('-','',str_replace(' ','T',$date))).'Z';
	}
	
	protected function _contains($txt1,$txt2) {
		$pos = stripos($txt1, $txt2);
		return ($pos !== false) ? 1:0;
	}
	
	function __destruct() {
	}
}

?>