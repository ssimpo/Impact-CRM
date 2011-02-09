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
class Acl extends Impact_Base {
	private $roles = array();
	private $application;
	public $accesslevel = 0;
	public $facebook;
	

	function __construct($application = null) {
		if (is_null($application)) {
			$application = Application::instance();
			$this->application = $application;
		} else {
			$this->application = $application;
			$application = Application::instance();
		}
		$this->facebook = $application->facebook;
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
		$this->roles['[FBUSER:'.$this->application->FBID.']'] = '[FBUSER:'.$this->application->FBID.']';
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
		preg_match_all('/(?<=(?:\[|\:))(\w+)(?=(?:\]|\:))/',$role,$matches);
		
		if (isset($matches[0])) {
			$attributes = $matches[0];
			if (count($attributes) > 2) {
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
		
		$handle = $this->factory($type);
		return $handle->test($subtype,$attributes);
	}
	
	/**
	 *	Factory method for classes, which are part of the calendar.
	 *
	 *	@public
	 *
	 *	@param	$className The name of the class to create.
	 *	@return	object	The requested class if it was found.
	 */
	public function factory($className) {
		$dir = $this->_get_include_directory();
		
		if (include_once $dir.'/Acl/class.'.str_replace('_','.',$className).'.php') {
			return new $className;
		} else {
			throw new Exception($className.' Class not found');
		}
	}
}

interface Acl_Test {
   public function __construct($application);
   public function test($type,$attributes);
}

class Acl_Test_Base {
   public function test($type,$attributes) {
        $functionName = '_test_'.strtolower($type);
        if (method_exists($this,$functionName)) {
            call_user_method($functionName,$this,$attributes);
        } else {
            return false;
        }
    }
}
?>