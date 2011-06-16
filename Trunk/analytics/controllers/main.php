<?php
if (!defined('DIRECT_ACCESS_CHECK')) {
	die('Direct access is not allowed');
}


function __autoload($className) {
    $paths = array(
        SITE_FOLDER.MODELS_DIRECTORY.DS,
        ROOT_BACK.MODELS_DIRECTORY.DS
    );
    
    foreach ($paths as $path) {
		$classFileName = str_replace('_',DS,$className).'.php';
        if (!@include_once $path.$classFileName) {
			$classFileName = str_replace(DS.'Base.php','.php',$classFileName);
			if (@include_once $path.$classFileName) {
				return true;
			}
		} else {
			return true;
		}
    }
    
    throw new Exception($className.' Class not found');
}


?>