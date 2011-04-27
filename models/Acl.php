<?php
/*
 *	Class for managing an Acl.  It will validate a given user against supplied roles.
 *	It will also allow for special user-groups that are assigned to various Facebook conditions or other
 *	concepts, such as people in a specfic locality.
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.6
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Impact
 */
class Acl extends ImpactBase {
	private $roles = array();
	private $application;
	public $accessLevel = 0;
	
	
	/**
	 *	Constructor.
	 *
	 *	@public
	 *	@param object|string $application The application object or the roles to load.
	 *	@param string $roles The roles to load.
	 */
	public function __construct($application=null,$roles=null) {
		if (is_string($application)) {
			$this->load_roles($application);
			$application=null;
		} 
		if (is_null($application)) {
			$application = $this->factory('Application');
			$this->application = $application;
		} else {
			$this->application = $application;
		}
		if (!is_null($roles)) {
			$this->load_roles($roles);
		} 
	
		$this->_get_facebook_object();
	}
	
	/**
	 *	Get the Facebook object and assign to property in application object.
	 *
	 *	Uses either the supplied application object or creates a new
	 *	reference to the main application object to try and aquire it,
	 *	otherwise set it to null.
	 *
	 *	@private
	 */
	private function _get_facebook_object() {
		if (!property_exists($this->application,'facebook')) {
			$application = $this->factory('Application');
			if (property_exists($application,'facebook')) {
				$this->application->facebook = $application->facebook;
			} else {
				$this->application->facebook = null;
			}
		}
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
			if (I::contains($rolesText,$role)) {
				return true;
			}
		}
		
		if (I::contains($rolesText,':')) {
			$rolesArray = explode(',',$rolesText);
			foreach ($rolesArray as $role) {
				if (I::contains($role,':')) {
					if ($this->test_special_role($role)) {
						$this->load_roles($role); //cache for later.
						return true;
					}
				}
			}
		}
		
		return false;
	}
	
	/**
	 *	Split a special role into its seperate parts.
	 *
	 *	Will split a special role into it's componant parts so they can
	 *	sent to the required class for parsing.  Eg. [FB:USER:93] will
	 *	return array('FB','USER',array('93')). Return false on failure.
	 *
	 *	@private
	 *	@param string $role Special role to split
	 *	@return array|boolean
	 */
	private function _split_special_role($role) {
		preg_match_all('/(?<=(?:\[|\:))([^:^\]^\[]+)(?=(?:\]|\:))/',$role,$matches);
		
		if (isset($matches[0])) {
			$attributes = $matches[0];
			if (count($attributes) > 1) {
				$type = array_shift($attributes);
				$subtype = array_shift($attributes);
				return array($type,$subtype,$attributes);
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
	 */
	protected function test_special_role($role) {
		list($type,$subtype,$attributes) = $this->_split_special_role($role);
		$handle = $this->factory('Acl_'.$type,array($this->application));
		return $handle->test($subtype,$attributes);
	}
}
?>