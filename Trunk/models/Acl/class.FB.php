<?php
class Acl_FB extends Acl_Test_Base implements Acl_Test {
    private $application;
    private $facebook;
    
    public function __construct($application) {
        if (property_exists($application,'facebook')) {
            $this->facebook = $application->facebook;
        } else {
            $fb_application = Application::instance();
	    $this->facebook = $fb_application->facebook;
        }
    }
    
    private function _test_user($attributes) {
        $fbid = (int) $attributes[0];
        if (property_exists($application,'FBID')) {
            if ($fbid == $this->application->FBID) {
                return true;
            }
        } else {
            if ($fbid == $this->facebook->getUser()) {
                return true;
            }
        }
        return false;
    }
    
    private function _test_friend($attributes) {
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