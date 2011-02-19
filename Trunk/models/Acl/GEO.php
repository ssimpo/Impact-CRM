<?php
/*
 *	Class for testing against a geographic location using MaxMind's GeoIP
 *	database.
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Impact
 */
class Acl_GEO extends Acl_TestBase implements Acl_Test {
        private $ip;
        private $geoCity=null;
        private $lookup=array();
        
        /**
         *	Constructor.
         *
         *	@public
         *	@param object $application The current (or other) application object.
         *	@return object Acl_GEO
         */
        public function __construct($application=null) {
                if (!defined('DS')) {
                        define('DS',DIRECTORY_SEPARATOR);
                }
                require_once 'Net'.DS.'GeoIP.php';
                
                if (!is_null($application)) {
                        $this->ip = $application->ip;
                } else {
                        $this->ip = $_SERVER['REMOTE_ADDR'];
                }
        }
    
        /**
         *      Do a lookup in the city database of a particular IP address.
         *
         *      Function allows for caching so repeated lookups on the same IP do
         *      not occur.  Lookup is cached for later reuse.  Database instance is
         *      also cached.
         *
         *      @private
         *      @param string $IP IP address in dotted-quad format.
         *      @return object The data returned from the database.
         */
        private function _city_lookup($IP) {
                if (is_null($this->geoCity)) {
                        $this->geoCity = Net_GeoIP::getInstance(
                                ROOT_BACK.'database'.DS.'geoCity.dat',
                                Net_GeoIp::MEMORY_CACHE
                        );
                }
        
                if (!isset($this->lookup[$IP])) {
                        $this->lookup[$IP] = $this->geoCity->lookupLocation($IP);
                }
                return $this->lookup[$IP];
        }
    
        private function _lat_long_distance($lat1, $lon1, $lat2, $lon2, $unit) {
                $unit = strtoupper($unit);
                $dist = rad2deg(acos(sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lon1 - $lon2))));
                $miles = $dist * 60 * 1.1515;
                
                switch ($unit) {
                        case 'KM':
                                return ($miles * 1.609344);
                        case 'M':
                                return $miles;
                        case 'N':
                                return ($miles * 0.8684);         
                }
                
                return null;
        }


        protected function _test_city($attributes) {
                $data = $this->_city_lookup($this->ip);
                return (strtoupper($data->city) == strtoupper($attributes[0]));
        }
    
        protected function _test_region($attributes) {
                $data = $this->_city_lookup($this->ip);
                return (strtoupper($data->rigion) == strtoupper($attributes[0]));
        }
    
        protected function _test_country($attributes) {
                $data = $this->_city_lookup($this->ip);
                return (strtoupper($data->countryCode) == strtoupper($attributes[0]));
        }
    
        protected function _test_radius($attributes) {
                $data = $this->_city_lookup($this->ip);
                
                $distance = $this->_lat_long_distance(
                        $data->latitude,$data->longitude,$attributes[0],$attributes[1],$attributes[3]
                );
                return ($distance <= (int) $attributes[2]);
        }
}
?>