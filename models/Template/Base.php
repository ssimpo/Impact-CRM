<?php
if (!defined('DIRECT_ACCESS_CHECK')) {
	die('Direct access is not allowed');
}

/**
 *	Template.Base class
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Template
 */
abstract class Template_Base Extends Base {
	protected $application;
	protected $mainApplication;
	protected $acl;
	
	public function __construct() {	
	}
	
	public function init($application,$mainApplication='') {
		$this->application = $application;
		if (empty($mainApplication)) {
			$this->mainApplication = $application;
		}
		$this->mainApplication = $mainApplication;
		$this->acl = $this->_get_application_item('acl');
	}
	
	protected function _show($attributes) {
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
			if (!I::contains($test,'['.$this->application['media'].']')) {
				return false;
			}
		}
		
		//Restrictions based on language - eg. en_gb, es, de, jp ...etc
		if (array_key_exists('lang',$attributes)) {
			$test = I::reformat_role_string($attributes['lang']);
			if (!I::contains($test,'['.$this->applications['lang'].']')) {
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
	
	private function _notblank($testers) {
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
	
	private function _ical(&$attributes) {
		
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
		if ((is_string($key)) || (is_numeric($key))) {
			if ((!empty($this->application)) && (is_array($this->application))) {
				if (array_key_exists($key,$this->application)) {
					return $this->application[$key];
				}
			}
			
			if ((!empty($this->mainApplication)) && (is_array($this->mainApplication))) {
				if (array_key_exists($key,$this->mainApplication)) {
					return $this->mainApplication[$key];
				}
			}
		}
		
		return '';
	}
	
}