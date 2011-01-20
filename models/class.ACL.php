<?php
/*
 *	Class for managing an Acl.  It will validate a given user against supplied roles.
 *	It will also allow for special user-groups that are assigned to various Facebook conditions or other
 *	concepts, such as people in a specfic locality.
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.5
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Impact
 */
class Acl {
	protected $roles = array();
	public $accesslevel = 0;
	public $facebook;
	public $FBID;

	function __construct() {
	}
	
	/**
	 *	Set the users roles according to supplied data.
	 *
	 *	Parse the supplied string into an array containing the users
	 *	roles.  Roles also gathered from supplied Facebook details.
	 *
	 *	@public
	 *	@param string $rolesText Text containing roles (eg. [ADMIN],[WEB] ...etc).
	 */
	public function load_roles($rolesText) {
		$rolesArray = explode(',',I::reformat_role_string($rolesText));
		foreach ($rolesArray as $role) {
			$this->roles[trim($role)] = trim($role);
		}
		$this->roles['[FBUSER:'.$this->FBID.']'] = '[FBUSER:'.$this->FBID.']';
	}
	
	/**
	 *	Test if the user is allowed.
	 *
	 *	Test the user against the supplied roles that are allolwed/
	 *	disallowed and return true/false.
	 *
	 *	@public
	 *	@param string $include Roles, which are included.
	 *	@param string $exclude Roles, which are excluded.
	 *	@return boolean Are they allowed or not?
	 */
	public function allowed($include='',$exclude='') {
		$numargs = func_num_args();
		
		$exclude='';
		if ($numargs > 1) {
			$exclude = func_get_arg(1);
		}
		
		if (($include == '') && ($exclude == '')) {
			return true;
		}
		
		if ($this->test_role($exclude)) {
			return false;
		} else {
			return $this->test_role($include);
		}
	}
	
	/**
	 *	Test if supplied text contains role, which user is in.
	 *
	 *	@protected
	 *	@param string $rolesText The text-string containing roles to test.
	 *	@return boolean 
	 */
	protected function test_role($rolesText) {
		$rolesText = I::reformat_role_string($rolesText);
		
		foreach ($this->roles as $role) {
			if (contains($rolesText,$role)) {
				return true;
			}
		}
		
		if (contains($rolesText,':')) {
			$rolesArray = explode(',',$rolesText);
			foreach ($rolesArray as $role) {
				if (contains($rolesText,':')) {
					if ($this->test_special_role($role)) {
						return true;
					}
				}
			}
		}
		
		return false;
	}
	
	/**
	 *	Test against a series of special roles.
	 *
	 *	Test against special roles, which generated on-the-fly from other
	 *	user-data or the application status.  Roles, which relate to Facebook
	 *	groups, browsers or geographic location.
	 *
	 *	@protected
	 *	@param string $role Special role to test against
	 *	@return boolean
	 *	@todo All the special cases listed below as stubs.
	 */
	protected function test_special_role($role) {
		preg_match_all('/\[([A-Za-z_]+)\:([0-9]+)\]/',$role,$matches);
		$type = $matches[1][0];
		$lookup = $matches[2][0];
		
		switch ($type) {
			case 'FBUSER':
				if ($lookup == $application[FBID]) {
					return true;
				}
				break;
			case 'FBFRIEND':
				if ($this->facebook->api_client->friend($this->FBID,$lookup)) {
					return true;
				}
				break;
			case 'FBLIKE':
				break;
			case 'FBGROUP':
				break;
			case 'FBEVENT_INVITED':
				break;
			case 'FBEVENT_ATTENDING':
				break;
			case 'FBEVENT_MAYBE':
				break;
			case 'FBEVENT_DECLINED':
				break;
			case 'FBEVENT_NOREPLY':
				break;
			case 'GEOTOWN':
				break;
			case 'GEOCOUNTRY':
				break;
			case 'BROWSER':
				break;
		}
		
		return false;
	}
}
?>