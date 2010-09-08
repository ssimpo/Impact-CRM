<?php
/**
*		iCal Interpreter, requires the SAVI parser for iCal/vCalendar/vCard
*		
*		@Author: Stephen Simpson
*		@Version: 0.0.1
*		@License: LGPL
*		
*/

class iCal_Interpreter {
	private $SAVI = '';
	private $data = '';
	private $block = '';
	private $subblock = '';
	
	public function __construct() {
		$this->SAVI = new SAVI_Parser();
		$this->SAVI->ical_set_element_handler(
			$this->SAVI,
			array($this,'_startTag'),array($this,'_endTag')
		);
		$this->SAVI->ical_set_character_data_handler(
			$this->SAVI,
			array($this,'_textContent')
		);
		$this->treePos = $data;
	}
	
	public function parse ($filename) {
		$this->data = array(
			'VEVENT'=>array(), 'VJOURNAL'=>array(), 'VTODO'=>array(), 
			'VTIMEZONE'=>array(), 'VFREEBUSY'=>array()
		);
		$this->SAVI->ical_parse($this->SAVI,$filename);
		echo '<pre>';print_r($this->data);echo '</pre>';
	}
	
	public function _startTag($parser,$name,$attributes,$content='') {
		switch ($name) {
			case 'VCALENDAR':
				$this->block = ''; $this->subblock = '';
				break;
			case 'VEVENT': case 'VJOURNAL': case 'VTODO' : case 'VTIMEZONE': case 'VFREEBUSY':
				$this->block = $name; $this->subblock = '';
				break;
			case 'VALRAM': case 'DAYLIGHT':
				$this->subblock = $name;
				$this->data[$this->block][$name] = array(0=>$carray);
				break;
			default:
				$carray = array('CONTENT'=>$content, 'ATTRIBUTES'=>$attributes);
				if (($this->subblock == '') && ($this->block == '')) {
					#$this->data[$name] = $carray;
					if (array_key_exists($name,$this->data)) {
						array_push($this->data[$name],$carray);
					} else {
						$this->data[$name] = array(0=>$carray);
					}
				} elseif ($this->subblock == '') {
					if (array_key_exists($name,$this->data[$this->block])) {
						array_push($this->data[$this->block][$name],$carray);
					} else {
						$this->data[$this->block][$name] = array(0=>$carray);
					}
				} else {
					if (array_key_exists($name,$this->data[$this->block][$this->subblock])) {
						array_push($this->data[$this->block][$this->subblock][$name],$carray);
					} else {
						$this->data[$this->block][$this->subblock][$name] = array(0=>$carray);
					}
				}
		}
		#echo '<p><b>TAG:</b> '.$name.'<br />'.$content.'</p>';
	}

	public function _endTag($parser,$name) {
		#$this->treePos = &$this->treePos['PARENT'];
	}

	public function _textContent($parser,$content) {
		#echo '<p>RAWTEXT: '.$content.'</p>';
	}
	
	private function _add_element_get_ref($key) {
		if (array_key_exists($key,$this->treePos)) {
			echo '<h1>1</h1>';
			$this->treePos[$key][count($this->treePos[$key])] = array();
			return $this->treePos[$key][count($this->treePos[$key])-1];
		} else {
			echo '<h1>2</h1>';
			$this->treePos[$key] = array('PARENT'=>$this->treePos);
			$this->treePos[$key][0] = array('PARENT'=>$this->treePos[$key]);
			return $this->treePos[$key][0];
		}
	}

}