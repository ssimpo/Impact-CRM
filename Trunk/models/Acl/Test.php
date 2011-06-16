<?php
if (!defined('DIRECT_ACCESS_CHECK')) {
	die('Direct access is not allowed');
}

interface Acl_Test {
	public function __construct($application);
	public function test($type,$attributes);
}
?>