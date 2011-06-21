<?php
defined('DIRECT_ACCESS_CHECK') or die;

abstract class Acl_TestBase {
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