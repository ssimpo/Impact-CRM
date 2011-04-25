<?php
interface Acl_Test {
	public function __construct($application);
	public function test($type,$attributes);
}
?>