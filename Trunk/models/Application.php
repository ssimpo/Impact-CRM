<?php
defined('DIRECT_ACCESS_CHECK') or die;

/**
 *	Main impact class
 *	
 *	Class containing a vast amount of functions and different concepts.
 *	Will need breaking down into separate classes for different aspects of the
 *	platform. Eg. Separate Database and Facebook classes?
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.5
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 
 *	@todo Breakdown into separate classes for different areas
 *	@package Impact
 *	@extends Impact_Base
 */
class Application extends Singleton {
	private static $instance;
	private $settings = array();
	public $facebook;
	public $fbsession;
	public $me;
	public $pageErrorCheck;
	
	/**
	 *	Initialization method.
	 *
	 *	@public
	 */
	public function setup() {
		$this->settings['fbid'] = 0;
		$this->_make_facebook_connection();
		$this->_language_detect();
		$this->_media_detect();
		$this->_user_access_detect();
		
		if (isset($_GET['page'])) {
			$this->pageName = strtolower(addslashes($_GET['page']));
		} else {
			$this->pageName = DEFAULT_HOMEPAGE;
		}
		
		$this->pageErrorCheck = $this->_get_page_request_info();
	}
	
	/**
	 *	Make a Facebook connection.
	 *
	 *	Connect to Facebook and return the session and Facebook objects
	 *	to Impact properties (Facebook and fbsession).
	 *
	 *	@public
	 *	@todo Update to the newest Facebook API.
	 */
	function _make_facebook_connection() {
		$this->facebook = new Facebook(array(
			'appId'  => FB_APPKEY,
			'secret' => FB_SECRET,
			'cookie' => true,
		));
		$this->fbsession = $this->facebook->getSession();
	
		if ($this->fbsession) {
			try {
				$this->uid = $this->facebook->getUser();
				$this->me = $this->facebook->api('/me');
			} catch (FacebookApiException $e) {
				error_log($e);
			}
		}
	}
	
	/**
	 *	Generic set property method.
	 *
	 *	Set the value of an application property.  Values are stored in
	 *	the application array and accessed via the __set and __get methods.
	 *
	 *	@public
	 */
	public function __set($property,$value) {
		$convertedProperty = I::camelize($property);
		$this->settings[$convertedProperty] = $value;
	}
	
	/**
	 *	Generic get property method.
	 *
	 *	Get the value of an application property.  Values are stored in
	 *	the application array and accessed via the __set and __get methods.
	 *
	 *	@public
	 */
	public function __get($property) {
		$convertedProperty = I::camelize($property);
		if (isset($this->settings[$convertedProperty])) {
			return $this->settings[$convertedProperty];
		} else {
			if ($property == 'settings') {
				return $this->settings;
			}
			throw new Exception('Property: '.$convertedProperty.', does not exist');
		}
	}
	
	/**
	 *	Test if a generic property is accessible
	 *
	 *	@public
	 *	@param string $property The property to test for
	 *	@return boolean
	 */
	public function property_exists($property) {
		return isset($this->settings[$property]);
	}
	
	/**
	 *	Set the current users access levels.
	 *
	 *	These are calculated from data stored in the database.
	 *
	 *	@private
	 *	@todo Needs a bit of work to improve it but works well and doesn't have any major security flaws.
	 */
	private function _user_access_detect() {
		$database = Database::instance();
		$this->roles = $database->get_roles($this->fbid);
		$this->accessLevel = $database->get_access($this->fbid);
		$this->acl = $this->factory('Acl',array($this));
	}
	
	/**
	 *	Check if current page request is valid.
	 *
	 *	Does the requested page exist? Does the current user have access.
	 *
	 *	@public
	 *	@return boolean Is the request valid.
	 */
	function _get_page_request_info() {
		$this->entityID = 0;
		$errorcheck = false;
		$database = Database::instance();
		
		$reader_roles = $database->create_roles_sql('readers');
	
		if (is_numeric($this->pageName)) {
			$errorcheck = $database->get_row(
				DEFAULT_CACHE_TIMEOUT,
				'SELECT Title FROM entities WHERE (ID='.$this->pageName.') AND '.$reader_roles
			);
			if ($errorcheck) {
				$this->entityID = $application['pageName'];
				$this->pageName = $errorcheck['Title'];
			} 
		} else {
			$errorcheck = $database->get_row(
				DEFAULT_CACHE_TIMEOUT,
				'SELECT ID FROM entities WHERE (Title="'.$this->pageName.'") AND '.$reader_roles
			);
			if ($errorcheck) {
				$this->entityID = $errorcheck['ID'];
			} 
		}
	
		return ($errorcheck)?true:false;
	}
	
	/**
	 *	Detect the media being used.
	 *
	 *	Will detect the media being used (eg. Desktop PC, Mobile, iPad,
	 *	Facebook, ...etc). Data is returned to the media property.
	 *	
	 *	@protected
	 *	@todo Add detection for wider range of media.
	 */
	protected function _media_detect() {
		$media = DEFAULT_MEDIA;
		
		if (isset($_GET['media'])) {
			$media = strtoupper(addslashes($_GET['media']));
		} else {
			//Auto-detection of Robots and FB needed :)
			if (defined('DOMAIN')) {
				if (substr(DOMAIN,0,2) == 'm.') { //Accessed the mobile subdomain
					$media = 'MOBILE';
				} /*elseif ($this->browser->ismobiledevice) {
					$media = 'MOBILE';
				}*/
			}
		}
		
		$this->media = I::reformat_role_string($media);
	}
	
	/**
	 *	Detect the language being used.
	 *
	 *	What is the native language of the current user?  Is detected from
	 *	the browser request headers and query-string.  Data is returned to
	 *	the language property.
	 *
	 *	@protected
	 *	@todo Allow Facebook language detection and user database lookup (for stored setting).
	 */
	protected function _language_detect() {
		$lang = DEFAULT_LANG;
		if (isset($_GET['lang'])) {
			$lang = strtolower(addslashes($_GET['lang']));
		} else {
			if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) { //Auto detection of first language
				$lang = explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
				$lang = $lang[0];
				$lang = str_replace('-','_',$lang);
			}
		}
		
		$this->language = strtoupper(I::reformat_role_string($lang));
	}
}
?>