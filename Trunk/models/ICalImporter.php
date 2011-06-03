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
	 private static $objectTranslation = array(
		'VEVENT' => 'event', 'VTIMEZONE' => 'timezone',
		'VTODO'=>'todo', 'VJOURNAL' => 'journal',
		'VALARM' => 'alarm', 'VFREEBUSY' => 'freebusy'
    );
	private $calId = '';
    
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
		
		return $this->calendar;
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
		$this->_set_cal_id();
		
		foreach ($this->data as $blockname => $block) {
			if (array_key_exists($blockname,self::$objectTranslation)) {
				$this->_store_objects($block,self::$objectTranslation[$blockname]);
			} else {
				//STUB
			}
		}
    }
	
	/**
	 *	Get or create the ID for the current Calendar being parsed.
	 *
	 *	@private
	 */
	private function _set_cal_id() {
		if (array_key_exists('PRODID',$this->data)) {
			if (array_key_exists('CONTENT',$this->data['PRODID'])) {
				$this->calId = md5($this->data['PRODID']['CONTENT']);
			}
		} else {
			$this->calId = md5(microtime());
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
		if (is_array($content)) {
			if (array_key_exists('CONTENT',$content)) {
				if (array_key_exists($tagname,self::$tagTranslation)) {
					$tagname = self::$tagTranslation[$tagname];
				}
				$functionName = 'set_'.strtolower($tagname);
				$data = $content['CONTENT'];
				unset($content['CONTENT']);
				return $object->{$functionName}($data,$content);
			}
		}
	
		return false;
    }
    
    /**
     *	Store a block within a Calendar-object against a specified block name.
     *
     *	@private
     *	@param object $object The Calendar-object to store block data in.
     *	@param array $content The content to store (usually in the format [0] => array() ...etc).
     *	@param string $blockname The block name to store it against.
     *	@return boolean Did it execute?
     */
    private function _store_block($object, $content, $blockname) {
		if (is_array($content)) {
			foreach ($content as $i => $data) {
				if (is_array($data)) {
					$block = $object->create_block($blockname);
					foreach ($data as $tagname => $content) {
						$this->_store_tag_data($block, $content, $tagname);
					}
				}
			}
			return true;
		}
		return false;
    }
	
	/**
     *	Store a Calendar object in a Calendar
     *
     *	@private
     *	@param array $block Array containing a series of object blocks.
     *	@param string $type The object types to store.
     */
    private function _store_objects($blocks,$type) {
	
		foreach ($blocks as $block) {
			$functionName = 'add_'.$type;
			$object = $this->calendar->{$functionName}();
			$object->set_cal_id($this->calId);
	    
			foreach ($block as $tagname => $content) {
				$stored = $this->_store_tag_data($object, $content, $tagname);
				if (!$stored) {
					$stored = $this->_store_block($object, $content, $tagname);
				};	
			}
			
		}
    }
    
}