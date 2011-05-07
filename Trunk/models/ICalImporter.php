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
     *	Paresed data is looped-through and various private functions called,
     *	which handle specfic data-types (eg. VEVENT or VTODO).
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
     *	Handle for the VTIMEZONE blocks.
     *
     *	@private
     *	@param array $block Array containing a series of VTIMEZONE blocks.
     */
    private function _handle_vtimezone($blocks) {
	foreach ($blocks as $vtimezone) {
	    $timezone = $this->calendar->add_timezone();
	    
	    foreach ($vtimezone as $tagname => $content) {
		if (array_key_exists('CONTENT',$content)) {
		    if ($tagname == 'TZID') {
			$timezone->set_id($content['CONTENT']);
		    } else {
			$functionName = 'set_'.strtolower($tagname);
			$timezone->{$functionName}($content['CONTENT']);
		    }
		} elseif (array_key_exists($tagname,self::$icalTimeZoneBlocks)) {
			
			$timeblock = $timezone->create_block($tagname);
			
			for ($i = 0; $i < count($content); $i++) {
			    $timeblock = $timezone->create_block($tagname);
			    foreach ($content[$i] as $subtagname => $subcontent) {
				
				if (array_key_exists('CONTENT',$subcontent)) {
				    if (array_key_exists($subtagname,self::$tagTranslation)) {
					$subtagname = self::$tagTranslation[$subtagname];
				    }
				    $functionName = 'set_'.strtolower($subtagname);
				    $timeblock->{$functionName}($subcontent['CONTENT']);
				}
				
			    }
			}
			
		} else {
		    // STUB
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
		if (array_key_exists('CONTENT',$content)) {
		    if ($tagname == 'UID') {
			$event->set_id(md5($content['CONTENT']));
		    } else {
			if (array_key_exists($tagname,self::$tagTranslation)) {
			    $tagname = self::$tagTranslation[$tagname];
			}
			$functionName = 'set_'.strtolower($tagname);
			$event->{$functionName}($content['CONTENT']);
		    }
		}
	    }

	}
    }
    
    
    
    
}