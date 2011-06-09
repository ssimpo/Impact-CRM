<?php
/*
*	@author Stephen Simpson <me@simpo.org>
*	@version 0.0.1
*	@license http://www.gnu.org/licenses/lgpl.html LGPL
*	@package Database
*
*/
class Database_SqlSequencer extends ImpactBase {
    private $settings = array();
    private $matrix;
    private $sql = array();
    
    public function __construct($entities='',$values='') {
        if (!empty($entities)) {
            $this->entities = $entities;
        }
        if (!empty($entities)) {
            $this->values = $values;
        }
    }
    
    public function __set($property,$value) {
		$convertedProperty = I::camelize($property);
        
        switch ($convertedProperty) {
            case 'values':
                $this->settings[$convertedProperty] = $this->_make_array($value);
                $this->settings['size'] = $this->_matrix_size();
                break;
            case 'enities':
                $this->settings[$convertedProperty] = $this->_make_array_of_array($value);
                break;
            case 'size':
                throw new Exception('Cannot set the matrix size');
                break;
            default:
                $this->settings[$convertedProperty] = $value;
                break;
        }
	}
    
    public function __get($property) {
		$convertedProperty = I::camelize($property);
        
        if (isset($this->settings[$convertedProperty])) {
			return $this->settings[$convertedProperty];
		} else {
			if ($property = 'settings') {
				return $this->settings;
			}
			throw new Exception('Property: '.$convertedProperty.', does not exist');
		}
	}
    
    public function exec($SQL) {
        $matrix = $this->_get_matrix();
        
        $sql = array();
        for ($i = 0; $i < count($this->matrix); $i++) {
            $sql[$i] = $SQL;
            foreach($this->matrix[$i] as $enity => $value) {
                $sql[$i] = str_replace($enity,$value,$sql[$i]);
            }
        }
        
        return $sql;
    }
    
    private function _get_matrix() {
        $this->_create_blank_matrix();
        
        for ($entityNo = 0; $entityNo < count($this->entities); $entityNo++) {
            $entity = $this->entities[$entityNo];
            
            $repeatNo = $this->_calc_repeat_number($entityNo);
            $counter = 0;
            while ($counter < $this->size) {
                
                for ($valueNo = 0; $valueNo < count($this->values[$entityNo]); $valueNo++) {
                    for ($ii = 0; $ii < $repeatNo; $ii++) {
                        $this->matrix[$counter][$entity] = $this->values[$entityNo][$valueNo];
                        $counter++;
                    }
                }
                
            }
        }
        
        return $this->matrix;
    }
    
    private function _calc_repeat_number($entityNo) {
        $repeatNo = 1;
        
        for ($i = 1; $i <= $entityNo; $i++) {
            $repeatNo *= count($this->values[$i-1]);
        }
        
        return $repeatNo;
    }
    
    private function _create_blank_matrix() {
        $this->matrix = array();
        
        for ($i = 0; $i < $this->size; $i++) {
            $this->matrix[$i] = $this->_create_blank_row();
        }
        
        return $this->matrix;
    }
    
    private function _create_blank_row() {
        $row = array();
        
        foreach($this->entities as $entity) {
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
    
    private function _matrix_size() {
        $total = 1;
        
        for ($i = 0; $i < count($this->values); $i++) {
            $total *= count($this->values[$i]);
        }
        
        return $total;
    }
}
?>