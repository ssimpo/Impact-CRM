<?php
/**
 *	Simple API for vCard/iCal/vCalendar, this is the iCal equivalent of SAX
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.3
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Calendar	
 */
class Savi_Parser {
	protected $lineNo = -1;
	protected $errNo = 0;
	protected static $errorMsgs = '';
	protected $handle = false;
	protected $lineFixer = true;
	protected $multiLineFixer = true;
	protected $uppercaseTags = true;
	protected $starter = '';
	protected $ender = '';
	protected $charHandle = '';
	
	/**
	*	Constructor for class
	*	@param Boolean $lineFixer Remove leading tabs from the start of lines
	*	@param Boolean $uppercaseTags Uppercase all the tags before passing to supplied processing functions
	*	@param Boolean $multiLinefixer Fix multiline content (not implemented yet)
	*	@return Object Reference to the object
	*/
	function __construct($lineFixer=true,$uppercaseTags=true,$multiLinefixer=true) {
		if (empty($this->errorMsgs)) {
			$this->_define_error_messages();
		}
		//$this->lineFixer = $linefixer;
		//$this->uppercaseTags = $uppercaseTags;
		//$this->multiLineFixer = $multiLineFixer;
		
		return $this;
	}
	
	/**
	*	Start parsing
	*	
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
	*	@param Object $parser Reference to parser to use
	*	@param String $filename The filename or resource to load
	*	@return Object Reference to the parser object or FALSE on failure
	*/
	public function load_data($parser,$filename) {
		$parser->handle = @fopen($filename,'r');
		if ($parser->handle) {
			$filename = '';
			
			if ($parser->multiLineFixer) {
				while (!feof($parser->handle)) {
					$filename .= fgets($parser->handle);
				}
			} else {
				while (!feof($parser->handle)) {
					$line = fgets($parser->handle);
					$parser->lineNo++;
					$parsed = $parser->_line_parser($line);
					$parser->_handle_line($parser,$parsed);	
				}
				fclose($parser->handle);
			}
		
			$parser->handle = false;
		} elseif ($filename == '') {
			$parser->errNo = 1;
				return false;
		}
		
		if ( (!$parser->handle) && ($filename != '') ) {
			$lines = array();
			if ($parser->multiLineFixer) {
				$lines = $this->_fix_multilines($filename);
			} else {
				$lines = explode("\n",$filename);
			}
		
			foreach ($lines as $line) {
				$parser->lineNo++;
				$parsed = $parser->_line_parser($line);
				$parser->_handle_line($parser,$parsed);
			}
		} 
		
		return $this;
	}
	
	/**
	*	Handle parsed line and call any defined function for handling current line
	*	
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
	*	@param String $data The raw data to parse for messey lines
	*	@return String() The lines as a series of array items, ready for parsing
	*/
	function _fix_multilines($data) {
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
				$attributes = $this->_value_list_parser(substr($line,$semiPos+1));
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
	*	Take an input string and split it according to standard iCal rules, hence:
	*		- split key1="value2";key2="value2" or key1=value2;key2=value2, into array
	*		- split value1,value2,value3... into an array
	*	
	*	@param String $valueList The string to split
	*	@return String() An array containing the split values or the original string if nothing to split
	*/
	protected function _value_list_parser($valueList) {
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
	
	private function _delimit_replace($content) {
		$content = str_replace('\,','~~//~@COMMA@~//~~',$content);
		$content = str_replace('\:','~~//~@COLON@~//~~',$content);
		$content = str_replace('\;','~~//~@SEMICOLON@~//~~',$content);
		return $content;
	}
	
	private function _delimit_unreplace($content) {
		$content = str_replace('~~//~@COMMA@~//~~',',',$content);
		$content = str_replace('~~//~@COLON@~//~~',':',$content);
		$content = str_replace('~~//~@SEMICOLON@~//~~',';',$content);
		$content = str_replace('\n',"\n",$content);
		$content = str_replace('\t',"\t",$content);
		return $content;
	}
	
	/**
	*	This is line fixer, removes tabs that may have crept into the line.  Just makes the parser more friendly, not strictly speaking conforming to the iCal standard.  Also fixes the different newline methods
	*	
	*	@param String $line The line to apply fixes to
	*	@return String The fixed line
	*/
	private function _fix_line($line) {
		preg_replace('/\A\t*/','',$line);
		//preg_replace('/[\r\f\n]/',"\n",$line);
		return $line;
	}
	
	/**
	*	Function to define the parser errors/codes - stores in a class static
	*/
	private function _define_error_messages() {
		$this->errorMsgs = array(
			-1,'No error',
			0,'Error code does not exist',
			1,'Could not open file or parse data content'
		);
	}
	
	/** 
	*	Equivalent to xml_set_element_handler, except the starter function will accept parsed content as well but can be ignored if required
	*	
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
	*	Equivalent to xml_set_character_data_handler.
	*	
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
	*	Convert a text string from UTF-8 to ISO-8859-1.  Exactly the same as the SAX parser equivalent
	*	
	*	@param String $data The UTF-8 encoded string to convert
	*	@return String A ISO-8859-1 encoded string
	*/
	public function utf8_decode($data) {
		return mb_convert_encoding($data,'UTF8','ISO-8859-1');
	}
	
	/** 
	*	Convert a text string from ISO-8859-1 to UTF-8. Exactly the same as the SAX parser equivalent
	*	
	*	@param String $data The ISO-8859-1 encoded string to convert
	*	@return String A UTF-8 encoded string
	*/
	public function utf8_encode($data) {
		return mb_convert_encoding($data,'ISO-8859-1','UTF8');
	}
	
	/**
	*	Equivalent of xml_get_error_code() in the standard PHP SAX Parser.  Will return the current error number if one exists
	*	
	*	@return Integer The current error number or -1 if no error has occurred
	*/
	public function ical_get_error_code() {
		return $this->errNo;
	}
	
	/**
	*	Equivalent to xml_get_error_string in the standard PHP SAX Parser. Will return error message 0 if no number is passed or error code does not exist
	*
	*	@param Integer $errNo The error string you want to lookup
	*	@return String The error string for the given code
	*/
	public function ical_get_error_string($errNo=0) {
		if (array_key_exists($errNo,$this->errorMsgs)) {
			return $this->errorMsgs($errNo);
		} else {
			return $this->errorMsgs(0);
		}
	}
	
	/**
	*	Equivalent to xml_get_current_line_number in the standard PHP SAX Parser. Will return the current line number the parser has got to in the parsing process.  Use to find out where an error happened.
	*
	*	@return Integer The line number the parser is up to
	*/
	public function ical_get_current_line_number() {
		return $this->lineNo;
	}
	
	/**
	*	Equivalent to xml_get_current_byte_index in the standard PHP SAX Parser. Will the current byte number the parser got to in the parsing process. Use to find out where an error happened.
	*
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
	*	Equivalent to xml_get_current_column_number in the standard PHP SAX Parser. Will the current column position the parser got to in the parsing process. Use to find out where an error happened.
	*
	*	@return Integer the column number
	*/
	public function ical_get_current_column_number() {
		return false;
	}

}