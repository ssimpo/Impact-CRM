<?php
/**
 *      The interface for all plugins
 *      
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Plugin
 */
interface Impact_Plugin {
    /**
     *  The implementation method for the plugin
     *
     *  @public
     *  @param Array $attributes All the attributes to run plugin against.  In
     *                              the format: param1=var1 ...etc.
     */
    public function run($attributes);
}
?>