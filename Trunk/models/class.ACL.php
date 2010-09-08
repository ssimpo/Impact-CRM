<?php
/*
		Class for managing an ACL.  It will validate a given user against supplied roles.
		It will also allow for special user-groups that are assigned to various Facebook conditions or other
		concepts, such as people in a specfic locality.
		
		@author Stephen Simpson <ssimpo@gmail.com>
		@copyright Stephen Simpson, <ssimpo@gmail.com>
		@License: LGPL
		@Version: 0.5.0
		
*/

class ACL {
	protected $roles = array();
	public $accesslevel = 0;
	public $facebook;
	public $FBID;

	function __construct() {
	}
	
	public function load_roles($rtext) {
		$rarray = explode(',',$this->reformatRole($rtext));
		foreach ($rarray as $role) {
			$this->roles[trim($role)] = trim($role);
		}
		$this->roles['[FBUSER:'.$this->FBID.']'] = '[FBUSER:'.$this->FBID.']';
	}
	
	public function allowed($include) {
		$numargs = func_num_args();
		
		$exclude='';
		if ($numargs > 1) {$exclude = func_get_arg(1);}
		
		if (($include == '') && ($exclude == '')) { return true; }
		
		if ($this->testRole($exclude)) { return false; } else { return $this->testRole($include); }
	}
	
	protected function testRole($rtext) {
		$rtext = $this->reformatRole($rtext);
		
		foreach ($this->roles as $role) {
			if (contains($rtext,$role)) { return true; }
		}
		
		if (contains($rtext,':')) {
			$rarray = explode(',',$rtext);
			foreach ($rarray as $role) {
				if (contains($rtext,':')) {
					if ($this->testSpecialRole($role)) { return true; }
				}
			}
		}
		
		return false;
	}
	
	protected function testSpecialRole($role) {
		preg_match_all('/\[([A-Za-z_]+)\:([0-9]+)\]/',$role,$matches);
		$type = $matches[1][0];
		$lookup = $matches[2][0];
		
		switch ($type) {
			case 'FBUSER':
				if ($lookup == $application[FBID]) { return true; }
				break;
			case 'FBFRIEND':
				if ($this->facebook->api_client->friend($this->FBID,$lookup)) { return true; }
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
	
	protected function reformatRole ($rtext) {
		$rtext = '['.str_replace(',','],[',$rtext).']';
		$rtext = str_replace('[[','[',$rtext);
		$rtext = str_replace(']]',']',$rtext);
	
		return $rtext;
	}
	
	function __destruct() {
	}
	
	
}
?>