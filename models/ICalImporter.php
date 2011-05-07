<?php
/**
 *	iCalendar Import Class
 *
 *	This function is used to import an iCalendar into the internal format
 *	of the Impact Platform.  A series of other classes are used as filters
 *	and interpreters of content.
 *
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.3
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Calendar
 */
class ICalImporter extends ImpactBase {
    private $parser = '';
    private $data = '';
    private $calendar = '';
    private static $tagTranslation = array(
	'DTSTART' => 'start_date', 'DTEND' => 'end_date',
	'DTSTAMP'=>'date_stamp', 'CREATED' => 'created_date',
	'LAST-MODIFIED' => 'last_modified_date'
    );
    private static $icalTimeZoneBlocks = array(
	'STANDARD' => true, 'DAYLIGHT' => true
    );
    
    /**
     *	Public construction class
     *
     *	@public
     */
    public function __construct() {
	$this->parser = $this->factory('iCalInterpreter');
	$this->calendar = $this->factory('Calendar');
    }
    
    /**
     *	Import iCalendar data and parse into PHP array.
     *
     *	@public
     *	@param string $path The path to the file being imported.
     *	@todo Allow direct importing of a string rather than via a file.
     *	@todo Ensure that it works for urls as well as standard file paths.
     */
    public function import($path) {
	$this->data = $this->parser->parse($path);
	$this->_data_parser();
    }
    
    /**
     *	Parse the data returned by the iCalInterpreter class.
     *
     *	Parsed data is looped-through and various private functions called,
     *	which handle specific data-types (eg. VEVENT or VTODO).
     *	
     *	@private
     */
    private function _data_parser() {
	foreach ($this->data as $blockname => $block) {
	    $handler = array($this,'_handle_'.strtolower($blockname));
	    if (is_callable($handler)) {
		call_user_func($handler,$block);
	    } else {
		// STUB
	    }
	}
    }
    
    /**
     *	Store content within a Calendar-object against a specified tag.
     *
     *	@private
     *	@param object $object The Calendar-object to store data in.
     *	@param array $content The content to store (usually in the format [CONTENT] => content).
     *	@param string $tagname The tag to store it against.
     *	@return boolean Did the storage command execute?
     */
    private function _store_tag_data($object, $content, $tagname) {
	if (array_key_exists('CONTENT',$content)) {
	    if (array_key_exists($tagname,self::$tagTranslation)) {
		$tagname = self::$tagTranslation[$tagname];
	    }
	    $functionName = 'set_'.strtolower($tagname);
	    return $object->{$functionName}($content['CONTENT']);
	}
	
	return false;
    }
    
    /**
     *	Handle for the VTIMEZONE blocks.
     *
     *	@private
     *	@param array $block Array containing a series of VTIMEZONE blocks.
     */
    private function _handle_vtimezone($blocks) {
	foreach ($blocks as $vtimezone) {
	    $timezone = $this->calendar->add_timezone();
	    
	    foreach ($vtimezone as $tagname => $content) {
		
		if ($tagname == 'TZID') {
		    $timezone->set_id($content['CONTENT']);
		} elseif (array_key_exists($tagname,self::$icalTimeZoneBlocks)) {
		    
		    for ($i = 0; $i < count($content); $i++) {
			$timeblock = $timezone->create_block($tagname);
			foreach ($content[$i] as $subtagname => $subcontent) {
			    $this->_store_tag_data($timeblock, $subcontent, $subtagname);
			}
		    }
		    
		} else {
		    $this->_store_tag_data($timezone, $content, $tagname);
		}
		
	    }
	    
	}
    }
    
    /**
     *	Handle for the VEVENT blocks.
     *
     *	@private
     *	@param array $block Array containing a series of VEVENT blocks.
     */
    private function _handle_vevent($blocks) {
	
	foreach ($blocks as $vevent) {
	    $event = $this->calendar->add_event();
	    
	    foreach ($vevent as $tagname => $content) {
		if ($tagname == 'UID') {
		    $event->set_id(md5($content['CONTENT']));
		} else {
		    $this->_store_tag_data($event, $content, $tagname);
		}
	    }
	    
	}
    }
    
    
    
    
}