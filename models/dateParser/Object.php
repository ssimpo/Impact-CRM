<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *      Date Parser interface
 *      
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.2
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Calendar
 */
interface DateParser_Object {
    
    /**
     *      Method to parse the date.
     * 
     *      @public
     *      @param string $date A string representation of the date.
     *      @param string $timezone Optional timezone string.
     *      @return date A PHP date object.
     */
    public function parse($date,$timezone);
}
?>