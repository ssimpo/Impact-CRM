<?php
/**
 *	Sites controller
 *
 *	Will load the site settings for current domain/directory.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.2
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 */

$sitesConfigPath = get_sites_config_path(ROOT_BACK.CONFIG_DIRECTORY.DS.SITES_FILE);
load_config($sitesConfigPath);

/**
 *	Get the path to the current site's config file.
 *
 *	@param string $path Path to the sites config file.
 *	@return string The path to current site's config file.
 */
function get_sites_config_path($path) {
	$config = simplexml_load_file($path);
	
	foreach ($config->param as $param) {
		if (strtolower($param['type']) == 'regx') {
			if (preg_match($param['value'],DOMAIN)) {
				return ROOT_BACK.CONFIG_DIRECTORY.DS.$param['src'];
			} elseif (preg_match($param['value'],ROOT_BACK)) {
				return ROOT_BACK.CONFIG_DIRECTORY.DS.$param['src'];
			}
		}
	}
}

/**
 * Load a config file
 *
 * 	Loads a config file (XML) and returns it's values as an array. String-values
 * 	are returned as strings and integer-values as integers.
 *
 *	@param String $path Location of the settings file.
 *	@return string()|integer()
 *	@todo Make it work with more complex data types.
 */
function load_config($path) {
	$config = simplexml_load_file($path);
        
	foreach ($config->param as $param) {
		if (!defined($param['name'])) {
			switch ($param['type']) {
				case 'string':
					define($param['name'],$param['value']);
					break;
				case 'integer':
					define($param['name'],(int) $param['value']);
					break;
				case 'boolean':
					$value = strtolower(trim($param['value']));
					switch ($value) {
						case 'true':
						case 'yes':
						case 'on':
							define($param['name'],true);
							break;
						case 'false':
						case 'no':
						case 'off':
							define($param['name'],false);
							break;
					}
					break;
			}
		}
	}
}
?>