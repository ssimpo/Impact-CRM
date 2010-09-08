<?php
function get_include_directory() {
	$debug = debug_backtrace();
	return dirname($debug[0][file]);
}

function require_all_once ($pattern) {
    foreach (glob($pattern) as $file) {
        require_once $file;
	}
}
?>