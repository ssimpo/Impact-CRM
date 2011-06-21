<?php
defined('DIRECT_ACCESS_CHECK') or die;

interface Acl_Test {
	public function __construct($application);
	public function test($type,$attributes);
}
?>