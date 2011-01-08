<?php
/**
 *	Global functions
 *
 *	These functions are used throughout the system and are essentially globals.
 *	Functions here should be kept to a minimum.  Also, these may be moved into
 *	an application class like Impact at some point to avoid use of globals.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	
 */


/**
 *	Get the the file location of the current class.
 */
function get_include_directory() {
	$debug = debug_backtrace();
	return dirname($debug[0][file]);
}

/**
 *	Load all the files (via require_once function) using supplied pattern.
 *
 *	Allows the loading of all files within a particular folder or
 *	following a specified pattern. Eg:
 *	<code>
 *	<?php require_all_once('includes/*.php'); ?>
 *	</code>
 *	This will include all the php files in the includes folder.
 *
 *	@param string $pattern The file search pattern for requiring.
 */
function require_all_once ($pattern) {
    foreach (glob($pattern) as $file) {
        require_once $file;
	}
}
?>