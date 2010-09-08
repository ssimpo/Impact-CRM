<?php
class Plugin {
    public static function factory ($type){
		if (include_once 'plugins/content/'.strtolower($type).'.php') {
			$classname = 'Plugin_' . $type;
			return new $classname;
		} else {
			return false;
		}
	}
}
?>