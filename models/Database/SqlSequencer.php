<?php
/*
*	@author Stephen Simpson <me@simpo.org>
*	@version 0.0.1
*	@license http://www.gnu.org/licenses/lgpl.html LGPL
*	@package Database
*
*/
class Database_SqlSequencer extends ImpactBase {
    private $enities;
    private $values;
    private $template;
    private $sql = array();
    
    public function __construct() {
    }
    
    public function exec($SQL,$entities,$values) {
        $this->template = $SQL;
        $this->entities = $this->_make_array($entities);
		$this->values = $this->_make_array_of_array($values);
        
        $total = $this->_array_matrix_size($this->entities);
        $matrix = $this->_get_matrix($this->entities,$this->values);
        
        $sql = array();
        for ($i = 0; $i < count($matrix); $i++) {
            $sql[$i] = $SQL;
            foreach($matrix[$i] as $enity => $value) {
                $sql[$i] = str_replace($enity,$value,$sql[$i]);
            }
        }
        
        return $sql;
    }
    
    private function _get_matrix($entities,$values) {
        $total = $this->_array_matrix_size($values);
        $matrix = $this->_create_blank_matrix($entities,$total);
        
        for ($entityNo = 0; $entityNo < count($entities); $entityNo++) {
            $entity = $entities[$entityNo];
            
            $repeatNo = $this->_calc_repeat_number($values,$entityNo);
            $counter = 0;
            while ($counter < $total) {
                
                for ($valueNo = 0; $valueNo < count($values[$entityNo]); $valueNo++) {
                    for ($ii = 0; $ii < $repeatNo; $ii++) {
                        $matrix[$counter][$entity] = $values[$entityNo][$valueNo];
                        $counter++;
                    }
                }
                
            }
        }
        
        return $matrix;
    }
    
    private function _calc_repeat_number($values,$entityNo) {
        $repeatNo = 1;
        
        for ($i = 1; $i <= $entityNo; $i++) {
            $repeatNo *= count($values[$i-1]);
        }
        
        return $repeatNo;
    }
    
    private function _create_blank_matrix($entities,$total) {
        $array = array();
        
        for ($i = 0; $i < $total; $i++) {
           $array[$i] =  $this->_create_blank_row($entities);
        }
        
        return $array;
    }
    
    private function _create_blank_row($entities) {
        $row = array();
        
        foreach($entities as $entity) {
            $row[$entity] = '';
        }
        
        return $row;
    }
    
    private function _make_array_of_array($data) {
		if (!is_array($data)) {
			$data = array($data);
		}
		$newArray = $data;
		
		foreach ($data as $key => $value) {
			if (is_array($value)) {
				$newArray[$key] = $value;
			} else {
				$newArray[$key] = array($value);
			}
		}
		
		return $newArray;
	}
    
    private function _make_array($data) {
		if (!is_array($data)) {
			return array($data);
		}
		return $data;
	}
    
    private function _array_matrix_size($array) {
        $total = 1;
        
        for ($i = 0; $i < count($array); $i++) {
            $total *= count($array[$i]);
        }
        
        return $total;
    }
}
?>