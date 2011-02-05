<?php
/**
 *	Global functions
 *
 *	These functions are used throughout the system and are essentially globals.
 *	Functions here should be kept to a minimum.  Also, these may be moved into
 *	an application class like Impact at some point to avoid use of globals.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1.1
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
			switch ($param['type']) {
				case 'string':
					define($param['name'],$param['value']);
					break;
				case 'integer':
					define($param['name'],(int) $param['value']);
					break;
			}
		}
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
	 *	@return	String The corresponding function name
	 */
	static public function function_to_variable($name) {
		$variable = '';
		$parts = explode('_',$name);
		if ((count($parts) == 1) || (count($parts) == 2)&&($parts[0] == '')) {
			return strtolower($name);	
		}
		
		foreach ($parts as $part) {
			if (strlen($part) > 1) {
				$firstLetter = substr($part,0,1);
				$afterFirstLetter = substr($part,1);
				$variable .= strtoupper($firstLetter).strtolower($afterFirstLetter);
			} elseif (strlen($part) == 1) {
				$variable .= strtoupper($part);
			}
		}
		$firstLetter = substr($name,0,1);
		if ($firstLetter == '_') {
			$variable = '_'.$variable;
		}
		
		return $variable;
	}
}
?>