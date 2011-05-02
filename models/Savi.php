<?php
/**
 *	Simple API for vCard/iCal/vCalendar, this is the iCal equivalent of SAX
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.3
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Calendar	
 */
class Savi {
	public $lineFixer = true;
	public $multiLineFixer = true;
	public $uppercaseTags = true;
	
	protected $lineNo = -1;
	protected $errNo = -1;
	protected static $errorMsgs = array(
		-1=>'No error',
		0=>'Error code does not exist',
		1=>'Could not open file or parse data content'
	);
	protected static $delimters = array(',' => '', ':' => '', ';' => '');
	
	protected $starter = '';
	protected $ender = '';
	protected $charHandle = '';
	
	
	/**
	*	Constructor for class
	*
	*	@public
	*	@param Boolean $lineFixer Remove leading tabs from the start of lines
	*	@param Boolean $uppercaseTags Uppercase all the tags before passing to supplied processing functions
	*	@param Boolean $multiLinefixer Fix multilingual content (not implemented yet)
	*	@return Object Reference to the object
	*/
	public function __construct($lineFixer=true,$uppercaseTags=true,$multiLinefixer=true) {
		$this->lineFixer = $lineFixer;
		$this->uppercaseTags = $uppercaseTags;
		$this->multiLineFixer = $multiLinefixer;
		
		foreach (self::$delimters as $key => $value) {
			if ($value == '') {
				self::$delimters[$key] = md5(microtime());
			}
		}
		
		return $this;
	}
	
	/**
	*	Start parsing
	*
	*	@public
	*	@param Object $parser Reference to the parser to use
	*	@param String $data The data to parse or a file/resource name to load as parsable data
	*	@return Boolean TRUE
	*/
	public function ical_parse($parser,$data) {
		$parser->load_data($parser,$data);
		return true;
	}
	
	/**
	*	Load and parse the content
	*
	*	@public
	*	@param Object $parser Reference to parser to use
	*	@param String $filename The filename or resource to load
	*	@return Object Reference to the parser object or FALSE on failure
	*/
	public function load_data($parser,$filename) {
		$handle = @fopen($filename,'r');
		if ($handle) {
			$filename = '';
			$data = $this->_parse_file_handle($handle);
			fclose($handle);
			$lines = $this->_split_ical_lines($parser,$data);
			$this->_parse_ical_lines($parser,$lines);
		} elseif ($filename == '') {
			$parser->errNo = 1;
			return false;
		}
		
		return $this;
	}
	
	/**
	 *	Parse a filehandle, loading the data into a string.
	 *
	 *	@private
	 *	@param filehandle $handle The filehandle to parse.
	 *	@return string The data returned from the file.
	 */
	private function _parse_file_handle($handle) {
		$data = '';
		while (!feof($handle)) {
			$data .= fgets($handle);
		}
		return $data;
	}
	
	/**
	 *	Split a string containing ical data into separate lines
	 *
	 *	@private
	 *	@param object $parser The parser object to use.
	 *	@param string $data Data to be split.
	 *	@return array The separate lines.
	 */
	private function _split_ical_lines($parser,$data) {
		$lines = array();
		$data = $this->_normalize_line_endings($data);
		if ($parser->multiLineFixer) {
			$lines = $this->_fix_multilines($data);
		} else {
			$lines = explode("\n",$data);
		}
		return $lines;
	}
	
	/**
	 *	Convert text content to UNIX format.
	 *
	 *	Different systems uses different line-enders.  This method
	 *	will attempt to convert all line-endings to the standard UNIX type,
	 *	which is: newline character.
	 *
	 *	@private
	 *	@param string $text Text to convert.
	 *	@return string Converted text.
	 */
	private function _normalize_line_endings($text) {
		$text = str_replace(
			array("\r\n","\n\r","\x1E","\x15"),
			array("\n","\n","\n","\n"),
			$text
		);
		$text = str_replace("\r", "\n", $text);
		return $text;
	}
	
	/**
	 *	Parse a series of ical lines (supplied as an array).
	 *
	 *	@private
	 *	@param object $parser The parser object to use.
	 *	@param array $lines Lines to be parsed.
	 */
	private function _parse_ical_lines($parser,$lines) {
		foreach ($lines as $line) {
			$parser->lineNo++;
			$parsed = $parser->_line_parser($line);
			$parser->_handle_line($parser,$parsed);
		}
	}
	
	/**
	*	Handle parsed line and call any defined function for handling current line
	*
	*	@private
	*	@param Object $parser Reference to parser object to use
	*	@param Array() $parsed The parsed contents of a line that needs handling
	*/
	private function _handle_line($parser,$parsed) {
		if ($parsed['tag'] == 'BEGIN') {
			$parsed['tag'] = trim($parsed['content']);
			$parsed['content'] = '';
			$parsed['rawtextcontent'] = '';
		}
		
		if ( ($parsed['tag'] == 'END') && (is_callable($parser->ender)) ) {
			call_user_func($parser->ender,$parser,trim($parsed['content']));
		} elseif (is_callable($parser->starter)) {
			call_user_func(
				$parser->starter,
				$parser,$parsed['tag'],$parsed['attributes'],$parsed['content']
			);
		}
				
		if ( (is_callable($this->charHandle)) && ($parsed['rawtextcontent'] !='') ) {
			call_user_func(
				$parser->charHandle,
				$parser,$parsed['rawtextcontent']
			);
		}
	}
	
	/**
	*	Fix for when a line is split with a newline in the actual feed.
	*
	*	@private
	*	@param string $data The raw data to parse for messy lines
	*	@return array The lines as a series of array items, ready for parsing
	*/
	private function _fix_multilines($data) {
		$olines = array();
		$lineNo = -1;
		
		$lines = explode("\n",$data);
		foreach ($lines as $line) {
			if ( (!preg_match('/\A[\tA-Za-z0-9\-_]+(?:\;|\:)/',$line)) && ($lineNo>0) ) {
				$olines[$lineNo] .= '\n'.$line;
			} else {
				$olines[++$lineNo] = $line;
			}
		}
		
		return $olines;
	}
	
	/**
	*	Parse one line of text into an array, which represents its content
	*
	*	@private
	*	@param String Line of text to parse
	*	@return Array() Parsed content in the following format:
	*		array(
	*			'tag' => The Tag name
	*			'attributes' => An array of the attributes in format attribute_name=>attribut_value (attribute_value can be a further array)
	*			'content' => The content, either as plain-text or parsed into an array if content was parsable.  Semicolon-seperated content is parsed as item_name=>item_value, like attributes (item_value can be a further array).  Comma -separated content is parsed as index=>item_value.
	*			'rawtextcontent' => The unparsed text content
	*		)
	*/
	private function _line_parser($line) {
		if ($this->lineFixer) {
			$line = $this->_fix_line($line);
		}
		$line = $this->_delimit_replace($line);
		
		$tag = '';
		$content='';
		$contentText = '';
		$attributes=array();
		
		$colonPos = strpos($line, ':');
		if ($colonPos !== false) {
			$tag = substr($line,0,$colonPos);
			$content = substr($line,$colonPos+1);
			
			$semiPos = strpos($tag,';');
			if ($semiPos !== false) {
				$attributes = $this->_value_list_parser(substr($tag,$semiPos+1));
				$tag = substr($line,0,$semiPos);
			}
			
			$contentText = $this->_delimit_unreplace($content);
			$content = $this->_value_list_parser($content);
		}
		
		if ($this->uppercaseTags) {
			$tag = strtoupper($tag);
		}
		return array(
			'tag' => trim($tag),
			'attributes' => $attributes,
			'content' => $content,
			'rawtextcontent' => $contentText
		);
	}
	
	/**
	*	Parse a string into it's value pairs.
	*
	*	Take an input string and split it according to standard iCal rules, hence:
	*		- split key1="value2";key2="value2" or key1=value2;key2=value2, into array.
	*		- split value1,value2,value3... into an array.
	*
	*	@private
	*	@param String $valueList The string to split
	*	@return String() An array containing the split values or the original string if nothing to split
	*/
	private function _value_list_parser($valueList) {
		$attributes = array();
		$semiPos = strpos($valueList,';');
		
		if (preg_match_all('/(?:\;|\:|\A)(.*?)\=\"{0,1}([^;^\"]+)\"{0,1}/',$valueList,$matches)) {
			for ($i=0; $i<count($matches[1]);$i++) {
				$attkey = trim($matches[1][$i]);
				$attval = $matches[2][$i];
						
				if (array_key_exists($attkey,$attributes)) { //Just in case we get duplicates
					$attributes[$attkey] .= ','.$attval;
				} else {
					$attributes[$attkey] = $attval;
				}
			}
			
			foreach ($attributes as $attkey => $attval) {
				$commaPos = strpos($attval, ',');
				if ($commaPos !== false) {
					$attributes[$attkey] = explode(',',$attval);
					foreach ($attributes[$attkey] as $key => $value) {
						$attributes[$attkey][$key] = $this->_delimit_unreplace($value);
					}
				} else {
					$attributes[$attkey] = $this->_delimit_unreplace($attval);
				}
			}
			return $attributes;
		} else {
			$commaPos = strpos($valueList, ',');
			if ($commaPos !== false) { 
				$valueList = explode(',',$valueList); 
				foreach ($valueList as $key => $value) {
					$valueList[$key] = $this->_delimit_unreplace($value);
				}
			} else {
				$valueList = $this->_delimit_unreplace($valueList);
			}
			return $valueList;
		}
	}
	
	/**
	 *	Delimit certain characters to avoid parsing issues.
	 *
	 *	@private
	 *	@param string $content String to delimit.
	 */
	private function _delimit_replace($content) {
		$find = array();
		foreach(array_keys(self::$delimters) as $character) {
			$find['\\'.$character] = self::$delimters[$character];
		}
		
		$content = str_replace(
			array_keys($find),
			array_values($find),
			$content
		);
		
		return $content;
	}
	
	/**
	 *	Undelimit certain characters to avoid parsing issues.
	 *
	 *	@note This reverses the action of _delimit_replace().
	 *
	 *	@private
	 *	@param string $content String to undelimit.
	 */
	private function _delimit_unreplace($content) {
		$content = str_replace(
			array_values(self::$delimters),
			array_keys(self::$delimters),
			$content
		);
		
		$content = str_replace('\n',"\n",$content);
		$content = str_replace('\t',"\t",$content);
		return $content;
	}
	
	/**
	*	Removes extra tabs from a line of text.
	*
	*	This is line fixer, removes tabs that may have crept into the line.  Just makes the parser more friendly, not strictly speaking conforming to the iCal standard.
	*
	*	@private
	*	@param String $line The line to apply fixes to
	*	@return String The fixed line
	*/
	private function _fix_line($line) {
		$line = preg_replace('/\A\t*/','',$line);
		return $line;
	}
	
	/** 
	*	Equivalent to xml_set_element_handler, except the starter function will accept parsed content as well but can be ignored if required
	*
	*	@public
	*	@param Object $parser Reference iCal to the parser to use
	*	@param Function() $starter The function to call with an opening tag.  Expected arguments are:
	*		- Object $parser Reference to the parser object
	*		- String $tag Name of the tag
	*		- String() $attributes The attributes of the tag
	*		- Content() $content The tag content will return an array if it is array content
	*	@param Function() $ender The function to call on closing tag. Expected arguments are:
	*		- Object $parser Reference to the parser object
	*		- String $tag Name of the closing tag
	*	@return Boolean TRUE
	*/
	public function ical_set_element_handler($parser,$starter,$ender) {
		$parser->starter = $starter;
		$parser->ender = $ender;
		return true;
	}
	
	/** 
	*	Equivalent to xml_set_character_data_handler in SAX parser.
	*
	*	@public
	*	@param Object $parser Reference iCal to the parser to use
	*	@param Function() $charHandle The function to call with text content.  Expected arguments are:
	*		- Object $parser Reference to the parser object
	*		- String $content The raw text
	*	@return Boolean TRUE
	*/
	public function ical_set_character_data_handler($parser,$charHandle) {
		$parser->charHandle = $charHandle;
		return true;
	}
	
	/** 
	*	Convert a text string to ISO-8859-1.
	*
	*	@note Exactly the same as the SAX parser equivalent
	*
	*	@public
	*	@param String $data The UTF-8 encoded string to convert
	*	@return String A ISO-8859-1 encoded string
	*/
	public function utf8_decode($data) {
		return mb_convert_encoding($data,'ISO-8859-1');
	}
	
	/** 
	*	Convert a text string to UTF-8.
	*
	*	@note Exactly the same as the SAX parser equivalent
	*
	*	@public
	*	@param String $data The ISO-8859-1 encoded string to convert
	*	@return String A UTF-8 encoded string
	*/
	public function utf8_encode($data) {
		return mb_convert_encoding($data,'UTF8');
	}
	
	/**
	*	Get the current error code.
	*
	*	Equivalent of xml_get_error_code() in the standard PHP SAX Parser.  Will return the current error number if one exists
	*
	*	@public
	*	@return Integer The current error number or -1 if no error has occurred
	*/
	public function ical_get_error_code() {
		return $this->errNo;
	}
	
	/**
	*	Get the error string for a given error code.
	*	
	*	Equivalent to xml_get_error_string in the standard PHP SAX Parser. Will return error message 0 if no number is passed or error code does not exist
	*
	*	@public
	*	@param Integer $errNo The error string you want to lookup
	*	@return String The error string for the given code
	*/
	public function ical_get_error_string($errNo=0) {
		if (array_key_exists($errNo,self::$errorMsgs)) {
			return self::$errorMsgs[$errNo];
		} else {
			return self::$errorMsgs[0];
		}
	}
	
	/**
	*	Get the current line number of the text being parsed
	*
	*	Equivalent to xml_get_current_line_number in the standard PHP SAX Parser. Will return the current line number the parser has got to in the parsing process.  Use to find out where an error happened.
	*
	*	@public
	*	@return Integer The line number the parser is up to
	*/
	public function ical_get_current_line_number() {
		return $this->lineNo;
	}
	
	/**
	*	Get the current byte position of the text being parsed.
	*
	*	Equivalent to xml_get_current_byte_index in the standard PHP SAX Parser. Will the current byte number the parser got to in the parsing process. Use to find out where an error happened.
	*
	*	@public
	*	@return Integer The byte number
	*/
	public function ical_get_current_byte_index() {
		if ($this->handle) {
			return ftell($this->handle);
		} else {
			return -1;
		}
	}
	
	/**
	*	Get the current column number of the text being parsed.
	*	
	*	Equivalent to xml_get_current_column_number in the standard PHP SAX Parser. Will the current column position the parser got to in the parsing process. Use to find out where an error happened.
	*
	*	@public
	*	@return Integer the column number
	*/
	public function ical_get_current_column_number() {
		return false;
	}

}