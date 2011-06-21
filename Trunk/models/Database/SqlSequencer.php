<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *  SQL Sequencer
 *
 *  Class to perform a series of search and replace operations on a SQL
 *  statement, producing an array of SQL statements.  The statements can be
 *  executed in-turn until a result is found.
 *   
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.2
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Database
 *
 */
class Database_SqlSequencer extends Base {
	private $settings = array();
	private $matrix;
	private $sql_array;
	
	 /**
	 *  Constructor
	 *  
	 *  @public
	 *  @param string|array The entities (values to search for in the SQL).
	 *  @param string|array The values (what to replace the entities with).
	 */
	public function __construct($entities='',$values='',$database='',$sql='') {
		if (!empty($entities)) {
			$this->entities = $entities;
		}
		if (!empty($entities)) {
			$this->values = $values;
		}
		if (!empty($database)) {
			$this->database = $database;
		}
		if (!empty($sql)) {
			$this->sql = $sql;
		}
		
		$application = Application::instance();
	}
	
	/**
	 *  Set class properties.
	 *
	 *  Properties are stored in the private settings array and can be
	 *  changed here.  values, entities, sql and size are treated differently, with
	 *  conversion methods used on the first three and error thrown for the
	 *  later.  Size cannot be set as it is a reflection of the matrix size.
	 *
	 *  @public
	 *  @param string $property The property to set.
	 *  @param mixed $value The value to set the property to.
	 */
	public function __set($property,$value) {
		$convertedProperty = I::camelize($property);
		
		switch ($convertedProperty) {
			case 'values':
				$this->settings[$convertedProperty] = $this->_make_array_of_array($value);
				$this->settings['size'] = $this->_matrix_size();
				break;
			case 'entities':
				$this->settings[$convertedProperty] = $this->_make_array($value);
				break;
			case 'size':
				throw new Exception('Cannot set the matrix size');
				break;
			case 'sql':
				$this->settings['template'] = $value;
				$this->_get_sql();
				break;
			default:
				$this->settings[$convertedProperty] = $value;
				break;
		}
	}
	
	/**
	 *  Get class properties.
	 *
	 *  Properties are stored in the private settings array and can be
	 *  accessed here.
	 *
	 *  @public
	 *  @param string $property The property to get.
	 *  @return mixed
	 */
	public function __get($property) {
		$convertedProperty = I::camelize($property);
		
		if ($convertedProperty == 'sql') {
			return $this->sql_array;
		} elseif (isset($this->settings[$convertedProperty])) {
			return $this->settings[$convertedProperty];
		} else {
			if ($convertedProperty == 'settings') {
				return $this->settings;
			} elseif ($convertedProperty == 'database') {
				$this->settings[$convertedProperty] = Database::instance();
				return $this->settings[$convertedProperty];
			}
			throw new Exception('Property: '.$convertedProperty.', does not exist');
		}
	}
	
	/**
	 *  Execute the current settings against the database.
	 *  
	 *  @public
	 *  @param string $SQL The SQL, which needs converting.
	 *  @return ADODBRecorset
	 */
	public function exec($SQL='') {
		if (!empty($SQL)) {
			$this->template = $SQL;
		}
		
		return $this->_get_sql(true);
	}
	
	/**
	 *  Calculate a series of SQL statements from class settings.
	 *
	 *  Take the class SQL statement and run a series of search and replace
	 *  operations against it, according to the values stored in $this->entities
	 *  and $this->values.  Results are returned in an array of SQL statements,
	 *  which can be run in sequence. If execute is set then the SQL is run
	 *  until a result is found and then the recordset is returned instead;
	 *  doing this is quicker since search and replace is only done until a
	 *  result is found.
	 *  
	 *  @public
	 *  @param boolean $execute Execute the statements or not?
	 *  @return array|ADODBRecordset An array of SQL statements or the recordset result.
	 */
	private function _get_sql($execute=false) {
		$this->_create_matrix();
		
		$this->sql_array = array();
		for ($i = 0; $i < count($this->matrix); $i++) {
			$this->sql_array[$i] = $this->template;
			foreach ($this->matrix[$i] as $enity => $value) {
				$this->sql_array[$i] = str_replace($enity,$value,$this->sql_array[$i]);
				
				if ($execute) {
					$rs = $this->database->get_rows(120,$this->sql_array[$i]);
					if (!empty($rs)) {
						return $rs;
					}
				}
				
			}
		}
		
		return $this->sql_array;
	}
	
	/**
	 *  Create an exececution-matrix.
	 *
	 *  Calculated from the supplied entities and values.  The matrix will be
	 *  used for search and replace operations and forms the order that these
	 *  should be done in.  Each row is a separate *result* and each column
	 *  in the row represents a search and replace operation to produce the
	 *  required result.
	 *  
	 *  @private
	 *  @return In the form: (('entity1=>'value1'...etc),('entity1=>'value2'...etc),...etc)
	 */
	private function _create_matrix() {
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
	
	/**
	 *  The number of times an entity is repeated in the matrix-group.
	 *
	 *  Calculated by taking the number of values assigned to all previous
	 *  entities in the sequence and multiplying them together.
	 *  
	 *  @private
	 *  @param integer $entityNo The entity-number, ie. it position or order-number.
	 *  @return integer
	 */
	private function _calc_repeat_number($entityNo) {
		$repeatNo = 1;
		
		for ($i = 1; $i <= $entityNo; $i++) {
			$repeatNo *= count($this->values[$i-1]);
		}
		
		return $repeatNo;
	}
	
	/**
	 *  Create a new blank execution matrix.
	 *
	 *  @note The return-value is not needed or generally used but is useful for testing.
	 *  
	 *  @private
	 *  @return array In the form: (('entity1=>''...etc),('entity1=>''...etc),...etc)
	 */
	private function _create_blank_matrix() {
		$this->matrix = array();
		
		for ($i = 0; $i < $this->size; $i++) {
			$this->matrix[$i] = $this->_create_blank_row();
		}
		
		return $this->matrix;
	}
	
	/**
	 *  Create a blank-row in the execution matrix.
	 *  
	 *  @private
	 *  @return array() In the form: ('entity1=>'','entity2'=>'',...etc)
	 */
	private function _create_blank_row() {
		$row = array();
		
		foreach ($this->entities as $entity) {
			$row[$entity] = '';
		}
		
		return $row;
	}
	
	/**
	 *  Create and array-of-array of the supplied data.
	 *
	 *  A string will be first turned into an array via the _make_array method.
	 *  An array or converted-string will be converted so that each item is
	 *  an array of it's own.
	 *  
	 *  @private
	 *  @param string|array $data The data to convert.
	 *  @return array
	 */
	private function _make_array_of_array($data) {
		if (!is_array($data)) {
			return array(array($data));
		}
		
		$hasArrays = false;
		$hasNoneArrays = false;
		foreach ($data as $value) {
			if (is_array($value)) {
				$hasArrays = true;
			} else {
				$hasNoneArrays = true;
			}
		}
		
		if (($hasArrays) && (!$hasNoneArrays)) {
			return $data;
		} elseif (!$hasArrays) {
			return array($data);
		} else {
			$array = array();
			foreach ($data as $value) {
				if (is_array($value)) {
					array_push($array,$value);
				} else {
					array_push($array,array($value));
				}
			}
			
			return $array;
		}
	}
	
	/**
	 *  Turn the supplied value into an array.
	 *
	 *  A string will be converted so that it is contained in a one item
	 *  array, where-as, arrays will be left untouched.
	 *  
	 *  @private
	 *  @param string|array $data The data to convert.
	 *  @return array
	 */
	private function _make_array($data) {
		if (!is_array($data)) {
			return array($data);
		}
		return $data;
	}
	
	/**
	 *  Calculate the number of rows in the execution matrix
	 *  
	 *  @private
	 *  @return integer
	 */
	private function _matrix_size() {
		$total = 1;
		
		for ($i = 0; $i < count($this->values); $i++) {
			$total *= count($this->values[$i]);
		}
		
		return $total;
	}
}
?>