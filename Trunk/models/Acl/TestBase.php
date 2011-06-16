<?php
if (!defined('DIRECT_ACCESS_CHECK')) {
	die('Direct access is not allowed');
}

class Acl_TestBase {
	public function test($type,$attributes) {
		$functionName = 'test_'.strtolower($type);
		if (method_exists($this,$functionName)) {
			return call_user_func(array($this,$functionName),$attributes);
		} else {
			return false;
		}
	}
}
?>