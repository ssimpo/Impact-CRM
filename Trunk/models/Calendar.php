<?php
/**
*	Calendar class
*		
*	@author Stephen Simpson <me@simpo.org>
*	@version 0.0.5
*	@license http://www.gnu.org/licenses/lgpl.html LGPL
*	@package Calendar
*/

class Calendar Extends ImpactBase {
	protected $objects = array();
	
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
					return $this->objects[$id];
					break;
			}
		}
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
}