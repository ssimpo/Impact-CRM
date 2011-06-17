<?php
if (!defined('DIRECT_ACCESS_CHECK')) {
	die('Direct access is not allowed');
}

/**
 *	Global functions
 *
 *	These functions are used throughout the system and are essentially globals.
 *	Functions here should be kept to a minimum.  Also, these may be moved into
 *	an application class like Impact at some point to avoid use of globals.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.2
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Impact
 */
class I {
	/**
	*	Get the the file location of the current class or running script file.
	*
	*	@static
	*/
	static public function get_include_directory() {
		$debug = debug_backtrace();
		return dirname($debug[0]['file']);
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
	*	@static
	*	@param string $pattern The file search pattern for requiring.
	*/
	static public function require_all_once($pattern) {
		foreach (glob($pattern) as $file) {
			require_once $file;
		}
	}
	
	/**
	*	Add square brackets between list items.
	*
	*	This method is used to make searching for key-values in an SQL
	*	database work.  Eg. PC,Mobile,Facebook would become: [PC],
	*	[Mobile],[Facebook].  You can then search for Like *[PC]* and
	*	not find 'PC' in the middle of a word.  Method will also get rid
	*	of double square-bracket notation '[[' used in Impact plugins.
	*	
	*	@static
	*	@public
	*	@param string|array $text The string to parse.
	*	@return string Parsed text with square brackets.
	*/
	static public function reformat_role_string($text) {
		if (is_array($text)) {
			$text = implode(',',$text);
		}
		
		$text = preg_replace('/\s{0,}\[\s{0,}/', '[', $text);
		$text = preg_replace('/\s{0,}\]\s{0,}/', ']', $text);
		$text = str_replace('][',',',$text);
		
		$text = str_replace('[','',$text);
		$text = str_replace(']','',$text);
		
		$text = str_replace(' ,',',',$text);
		$text = str_replace(', ',',',$text);
		$text = str_replace(',,',',',$text);
		
		$text = '['.str_replace(',','],[',trim($text)).']';
		return $text;
	}
	
	/**
	 *	Load a config file
	 *
	 *	Loads a config file (XML) and returns it's values as an array. String-values
	 *	are returned as strings and integer-values as integers.
	 *
	 *	@static
	 *	@public
	 *	@param String $path Location of the settings file.
	 *	@return string()|integer()
	 *	@todo Make it work with more complex data types.
	 */
	static public function load_config($path) {
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
							case 'true': case 'yes': case 'on':
								define($param['name'],true);
								break;
							case 'false': case 'no': case 'off':
								define($param['name'],false);
								break;
						}
						break;
				}
			}
		}
	}
	
	/**
	 *
	 *	Trim the contents of an array.
	 *
	 *	Array elements are trimmed and blank entries removed.
	 *	
	 *	@static
	 *	@public
	 *	@param Array() $array The array to trim
	 *	@return	Array()
	 */
	static public function array_trim($array) {
		$trimmed = array();
		$is_numeric_indexed = I::_is_numeric_indexed_array($array);
		
		foreach ($array as $key => $value) {
			$value = trim($value);
			if ($value != '') {
				if ($is_numeric_indexed) {
					array_push($trimmed,$value);
				} else {
					$trimmed[$key] = $value;
				}
			}
		}
		
		return $trimmed;
	}
	
	/**
	 *
	 *	Test if an array is indexed numerically.
	 *	
	 *	@static
	 *	@private
	 *	@param Array() $array The array to test.
	 *	@return	Boolean
	 */
	static private function _is_numeric_indexed_array($array) {
		$is_numeric_indexed = true;
		
		foreach ($array as $key => $value) {
			if (!is_numeric($key)) {
				$is_numeric_indexed = false;
			}
		}
		
		return $is_numeric_indexed;
	}
	
	/**
	 *
	 *	Convert a function-name into a variable name.
	 *
	 *	According to the Impact coding standards, functions/methods
	 *	are named with words separated with underscores;
	 *	variables/properties are named using camelCase.  This function
	 *	allows conversion so that the magic method __set()/__get() work
	 *	as expected.
	 *	
	 *	@static
	 *	@public
	 *	@param String $name Name of function.
	 *	@return	String The corresponding variable name
	 */
	static public function camelize($name) {
		if (!I::is_camelcase($name)) {
			$name = strtolower($name);
		}
		$variable = ucwords(str_replace('_',' ',$name));
		$variable_length = strlen($variable);
		$variable = lcfirst(ltrim($variable));
		$variable = str_repeat('_',$variable_length-strlen($variable)).$variable;
		$variable = rtrim($variable);
		$variable = $variable.str_repeat('_',$variable_length-strlen($variable));
		return str_replace(' ','',$variable);
	}
	
	/**
	 *	Check if a string is in camelcase or not.
	 *
	 *	@static
	 *	@public
	 *	@param String $text The string to test
	 *	@return Boolean
	 */
	static public function is_camelcase($text) {
		return ((strtolower($text) != $text)&&(strtoupper($text) != $text));	
	}
	
	/**
	 *
	 *	Convert a variable name into a function-name.
	 *
	 *	According to the Impact coding standards, functions/methods
	 *	are named with words separated with underscores;
	 *	variables/properties are named using camelCase.  This function
	 *	allows conversion so that the magic method __set()/__get() work
	 *	as expected.
	 *	
	 *	@static
	 *	@public
	 *	@param String $name Name of variable.
	 *	@return	String The corresponding function name
	 */
	static public function uncamelize($name) {
		return strtolower(implode('_',preg_split('/(?<=\\w)(?=[A-Z])/', $name)));
	}
	
	/**
	 *	Is the one snippet of text contained within another.
	 *
	 *	@static
	 *	@public
	 *	@param String $text1 The string to search within.
	 *	@param String $text2 The string to search for.
	 *	@return Boolean
	*/
	static public function contains($text1,$text2) {
		return ((stristr($text1,$text2) !== false) ? true:false);
	}
}
?>