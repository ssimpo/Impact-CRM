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
    private static $icalToImpactDates = array(
	'DTSTART' => 'startDate', 'DTEND' => 'endDate',
	'DTSTAMP'=>'dateStamp', 'CREATED' => 'createdDate',
	'LAST-MODIFIED' => 'lastModifiedDate'
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
		    if (array_key_exists($tagname,self::$icalToImpactDates)) {
			$event->set_date(
			    self::$icalToImpactDates[$tagname],
			    $content['CONTENT']
			);
		    } elseif ($tagname == 'UID') {
			$event->set_id(md5($content['CONTENT']));
		    } else {
			$functionName = 'set_'.strtolower($tagname);
			$event->{$functionName}($content['CONTENT']);
		    }
		}
	    }
	}
    }
    
    
    
    
}