<?php
/*
 *	Class for testing current or specified users against various Facebook
 *	conditions.  Can be used to generate special on-the-fly roles, which a
 *	user is tested against.
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.5
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Impact
 */
class Acl_FB extends Acl_TestBase implements Acl_Test {
    private $application;
    private $facebook;
    
    /**
     *		Constructor.
     *
     *		@public
     *		@param object $application The current (or other) application object.
     *		@return object Acl_FB
     */
    public function __construct($application=null) {
	if (is_null($application)) {
	    $application = $this->factory('Application');
	}
	$this->application = $application;
	
        if (property_exists($application,'facebook')) {
            $this->facebook = $application->facebook;
        } else {
	    $fb_application = $this->factory('Application');
	    $this->facebook = $fb_application->facebook;
        }
    }
    
    /**
     *		Is the current user the one specified in the supplied attributes.
     *
     *		@public
     *		@param array[] $attributes The Facebook-ID of the user in form $attributes[0] = ID.
     *		@return boolean
     */
    public function _test_user($attributes) {
        $fbid = (int) $attributes[0];
	
	try {
	    if ($fbid == $this->application->FBID) {
                return true;
            }
	} catch (Exception $e) {
	    if ($fbid == $this->facebook->getUser()) {
                return true;
            }
	}

        return false;
    }
    
    /**
     *		Is the current user a fiend of the user supplied in the attributes.
     *
     *		@private
     *		@param array $attributes The Facebook-ID of the friend in the form $attributes[0] = ID.
     *		@return boolean
     */
    public function _test_friend($attributes) {
        $friendID = $attributes[0];
        if (is_numeric($friendID)) {
            $friendID = (int) $fbid;
        }
        
        if ($this->facebook->api_client->friend($this->application,$friendID)) {
	    return true;
	}
        return false;
    }
}
?>