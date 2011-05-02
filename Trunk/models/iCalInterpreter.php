<?php
/**
 *	iCal Interpreter, requires the SAVI parser for iCal/vCalendar/vCard
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.5
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Calendar
 *
 *	@todo Test with xCal using SAX and make it work for this.
 */
class iCalInterpreter {
	private $savi = '';
	private $data = array();
	private $block = '';
	private $subblock = '';
	private $treePos;
	
	public function __construct() {
		$this->savi = new Savi();
		
		$this->savi->ical_set_element_handler(
			$this->savi,
			array($this,'start_tag'),array($this,'end_tag')
		);
		$this->savi->ical_set_character_data_handler(
			$this->savi,
			array($this,'text_content')
		);
		$this->treePos =& $this->data;
	}
	
	public function parse($filename) {
		$this->savi->ical_parse($this->savi,$filename);
		return $this->data;
	}
	
	public function start_tag($parser,$name,$attributes,$content='') {
		$handler = array($this,'_handle_'.strtolower($name));
		if (is_callable($handler)) {
			call_user_func($handler,$this,$name,$attributes,$content);
		} else {
			$this->_new_data($name,$attributes,$content);
		}
	}
	
	/**
	 *	Handle a VCALENDAR tag.
	 *
	 *	@private
	 */
	private function _handle_vcalendar() {
		$this->block = '';
		$this->subblock = '';
		$this->treePos =& $this->data;
	}
	
	/**
	 *	Handle a VEVENT tag.
	 *
	 *	@private
	 */
	private function _handle_vevent() {
		$this->_new_block('VEVENT');
	}
	
	/**
	 *	Handle a VTODO tag.
	 *
	 *	@private
	 */
	private function _handle_vtodo() {
		$this->_new_block('VTODO');
	}
	
	/**
	 *	Handle a VJOURNAL tag.
	 *
	 *	@private
	 */
	private function _handle_vjournal() {
		$this->_new_block('VJOURNAL');
	}
	
	/**
	 *	Handle a VTIMEZONE tag.
	 *
	 *	@private
	 */
	private function _handle_vtimezone() {
		$this->_new_block('VTIMEZONE');
	}
	
	/**
	 *	Handle a VFREEBUSY tag.
	 *
	 *	@private
	 */
	private function _handle_vfreebusy() {
		$this->_new_block('VFREEBUSY');
	}
	
	/**
	 *	Handle a VALARM tag.
	 *
	 *	@private
	 */
	private function _handle_valarm() {
		$this->_new_subblock('VALARM');
	}
	
	/**
	 *	Handle a DAYLIGHT tag.
	 *
	 *	@private
	 */
	private function _handle_daylight() {
		$this->_new_subblock('DAYLIGHT');
	}
	
	/**
	 *	Handle a STANDARD tag.
	 *
	 *	@private
	 */
	private function _handle_standard() {
		$this->_new_subblock('STANDARD');
	}
	
	/**
	 *	Generate a new block element in the current data tree.
	 *
	 *	@private
	 *	@param string $name The name of the block element to create.
	 */
	private function _new_block($name) {
		$this->block = $name;
		$this->subblock = '';
		$this->treePos =& $this->data;
		
		$this->_new_block_generic($name);
	}
	
	/**
	 *	Generate a new subblock element at current tree position.
	 *
	 *	@private
	 *	@param string $name The name of the subblock element to create.
	 */
	private function _new_subblock($name) {
		if ($this->subblock != '') { //Return to parent
			$this->treePos =& $this->data[$this->block];
			$this->treePos =& $this->treePos[count($this->treePos)-1];
		}
		$this->subblock = $name;
		$this->_new_block_generic($name);
	}
	
	/**
	 *	Generate a new block element at current tree position.
	 *
	 *	@private
	 *	@param string $name The name of the block element to create.
	 */
	private function _new_block_generic($name) {
		if (!array_key_exists($name,$this->treePos)) {
			$this->treePos[$name] = array();
		}
		$this->treePos =& $this->treePos[$name];
		$end = array_push($this->treePos,array());
		$this->treePos =& $this->treePos[$end-1];
	}
	
	/**
	 *	Generate a new data element at current tree position.
	 *
	 *	@private
	 *	@param string $name The name of the block element to create.
	 */
	private function _new_data($name,$attributes,$content) {
		if (!is_array($attributes)) {
			$attributes = array();
		}
		$attributes['CONTENT'] = $content;
		
		if (!array_key_exists($name,$this->treePos)) {
			// Standard and usual method
			$this->treePos[$name] = $attributes;
		} else {
			// Rare situation where tag already exist in given block (ie. multiple occurrences of tag)
			if (array_key_exists(0,$this->treePos[$name])) {
				$end = count($this->treePos[$name]);
				$this->treePos[$name][$end] = $attributes;
			} else {
				$this->treePos[$name] = array(
					0 => $this->treePos[$name],
					1 => $attributes
				);
			}
		}
	}

}