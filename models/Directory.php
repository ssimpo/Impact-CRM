<?php
defined('DIRECT_ACCESS_CHECK') or die;

/*
 *	Directory handling class
 *
 *	Directory Open/Close, browsing and parsing operations.
 *	
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.6
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Filesystem
 */
class Filesystem_Directory extends Filesystem {
	
	public function __construct() {
		$this->_init();
	}
	
	private function _init($path='',$filename='') {
		
	}
	
}
?>