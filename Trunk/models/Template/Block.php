<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *	Class for <template:block />
 *	
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Template
 */
class Template_Block Extends Template_Base implements Template_Object {
    
	/**
	 *	Parse <template:block />
	 *
	 *	Hide or show the content according to its ACL attributes.  Tag offers
	 *	a good way to restrict content to particular users or to display data
	 *	on particular days/times.
	 *
	 *	@public
	 *	@param array $match text containing the attributes.
	 *	@return string The block parsing results.
	 */
	public function parse($match) {
		if ($this->_show($match['attributes'])) {
			return $match['content'];
		} else {
			return '';
		}
	}

}