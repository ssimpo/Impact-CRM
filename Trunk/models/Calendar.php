<?php
if (!defined('DIRECT_ACCESS_CHECK')) {
	die('Direct access is not allowed');
}

/**
 *	Calendar class
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.5
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Calendar
 */
class Calendar Extends Base {
	protected $objects = array();
	protected $objectToImpactIds = array();
	protected $impactToObjectIds = array();
	protected $timezones = array();
	
	/**
	 *	Method factory.
	 *
	 *	@public
	 *	@param string $name The name of the method called (eg. add_event)
	 *	@param array $arguments The arguments passed to the method.
	 *	@return object Calendar class of specified type
	 */
	public function __call($name,$arguments) {
		$parts = explode('_',strtolower($name));
		if (count($parts) == 2) {
			switch ($parts[0]) {
				case 'add':
					$id = $this->_rnd_string();
					$this->objects[$id] = $this->factory('Calendar_'.$parts[1]);
					$this->objects[$id]->set_id($id);
					$this->objects[$id]->calendar = $this;
					return $this->objects[$id];
					break;
			}
		}
		
		parent::__call($name,$arguments);
	}
	
	public function set_timezone($object,$tzid,$calId) {
		$id = md5($tzid.'@'.$calId);
		$this->timezones[$id] = $object->get_id();
	}
	
	/**
	 *	Get a Calendar object via it's ID.
	 *
	 *	@public
	 *	@param string $id The ID of the object to get.
	 *	@return object|boolean The object returned or false on not found.
	 */
	public function get($id) {
		
		if (!$this->_is_md5($id)) {
			$id = md5($id);
			if (array_key_exists($id,$this->objectToImpactIds)) {
				$id = $this->objectToImpactIds[$id];
			}
		}
		if (array_key_exists($id,$this->timezones)) {
			$id = $this->timezones[$id];
		}
		
		if (array_key_exists($id,$this->objects)) {
			return $this->objects[$id];
		}
		
		return false;
	}

	/**
	 *	Set-up a lookup ID from a Calendar Object's own ID
	 *
	 *	Impact assigns every Calendar Object an ID on creation. Sometimes
	 *	objects have their own ID internally.  This will allow cross-referencing
	 *	of one ID against the other.
	 *
	 *	@public
	 *	@param object $object The Calendar Object.
	 *	@param string $id The Object's own internal ID.
	 *	
	*/
	public function set_id_lookup($object,$id) {
		$objectID = md5($id);
		$this->impactToObjectIds[$object->get_id()] = $objectID;
		$this->objectToImpactIds[$objectID] = $object->get_id();
	}
	
	/**
	 *	Create a random generic string (UNID).
	 *
	 *	@protected
	 *	@return string*32 Random, 32-digit hexadecimal string
	 */
	protected function _rnd_string() {
		return md5(chr(rand(1,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)));
	}
	
	/**
	 *	Test whether a string could be a valid MD5 hash or not.
	 *
	 *	@private
	 *	@param string $hash The string to test.
	 *	@return boolean
	 */
	private function _is_md5($hash) {
		if (strlen($hash) == 32) {
			try {
				$test = hexdec($hash);
				return true;
			} catch (Exception $e) {
				return false;
			}
		} else {
			return false;
		}
	}
}