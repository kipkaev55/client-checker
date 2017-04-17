<?php
/**
 * client-checker
 *
 * Released under the MIT license
 */
namespace ClientChecker;
use UAParser\Parser;
use GeoIp2\Database\Reader;
use SypexGeo\Reader as SxReader;

class Client
{
    protected $ua = null;
    protected $ip = null;
    protected $db = null;
    protected $locale = null;
    protected $regexes = null;

    const SX_GEO             = 'sypex';
    const GEO_IP             = 'geoip';
    const FIRST              = 'sypex';
    const MESSAGE_DB_NOT_SET = 'DB not set';

    public function __construct($ua, $ip, $db, $locale = 'en', $regexes = null) {
        $this->ua = $ua;
        $this->ip = $ip;
        $this->db = $db;
        $this->locale = $locale;
        $this->regexes = $regexes;
    } 

    public function isMobile()
    {
        $notMobile = array(
              'Other',
              'Spider',
              'WebTV',
              'Nintendo Wii',
              'Nintendo DS',
              'PlayStation 3',
              'PlayStation Portable'
          );
        $parser = Parser::create($this->regexes);
        $result = $parser->parse($this->ua);
        $isMobile = !in_array($result->device->family, $notMobile);
        return $isMobile;
    }

    public function getOs()
    {
        $parser = Parser::create($this->regexes);
        $result = $parser->parse($this->ua);
        $system = ($result->os->toString() == "Other") ? "Unknown" : $result->os->toString();
        return $system;
    }

    public function getBrowser()
    {
        $parser = Parser::create($this->regexes);
        $result = $parser->parse($this->ua);
        $browser = ($result->ua->toString() == "Other") ? "Unknown" : $result->ua->toString();
        return $browser;
    }

    public function getIpData()
    {
        if(self::FIRST == 'geoip') {
            $data = $this->getGeoIp();

            if($data['country'] == 'UN') {
                $data['country'] = $this->getSxGeo()['country'];
            }
            if($data['city'] == 'Unknown') {
                $data['city'] = $this->getSxGeo()['city'];
            }

        } else if (self::FIRST == 'sypex') {
            $data = $this->getSxGeo();

            if($data['country'] == 'UN') {
                $data['country'] = $this->getGeoIp()['country'];
            }
            if($data['city'] == 'Unknown') {
                $data['city'] = $this->getGeoIp()['city'];
            }

        } else {
            $data = self::MESSAGE_DB_NOT_SET;
        }
        return $data;
    }

    protected function getGeoIp()
    {
        if(isset($this->db[self::GEO_IP])) {
            $reader = new Reader($this->db[self::GEO_IP], array($this->locale));
            $data = array();
            try {
                $resp = $reader->city($this->ip);
                $data['country'] = (($resp->country->isoCode != null) ? $resp->country->isoCode : "UN");
                $city = null;

                if($resp->city->name != null) {
                    $city = $resp->city->name;
                } elseif($resp->city->names['en'] != null) {
                    $city = $resp->city->names['en'];
                } elseif($resp->mostSpecificSubdivision->name != null) {
                    $city = $resp->mostSpecificSubdivision->name;
                } elseif($resp->mostSpecificSubdivision->names['en'] != null) {
                    $city = $resp->mostSpecificSubdivision->names['en'];
                } else {
                    $city = "Unknown";
                }
                $data['city'] = $city;

            } catch (\GeoIp2\Exception\AddressNotFoundException $e) {
                if((ip2long($this->ip) >= 167772160 && ip2long($this->ip) <= 184549375)
                    || (ip2long($this->ip) >= 2886729728 && ip2long($this->ip) <= 2887778303)
                    || (ip2long($this->ip) >= 3232235520 && ip2long($this->ip) <= 3232301055)) { //networks classes A,B,C
                    $data['country'] = 'LO';
                    $data['city'] = 'Local Network';
                } elseif((ip2long($this->ip) >= 2130706432 && ip2long($this->ip) <= 2147483647)){
                    $data['country'] = 'LO';
                    $data['city'] = 'Loopback';
                } else {
                    $data['country'] = 'UN';
                    $data['city'] = 'Unknown';
                }
            }    
        } else {
            $data = self::MESSAGE_DB_NOT_SET;
        }
        return $data;
    }

    protected function getSxGeo()
    {
        if(isset($this->db[self::GEO_IP])) {
            $reader = new SxReader($this->db[self::SX_GEO], $this->locale);
            $data = $reader->getGeo($this->ip);
        } else {
            $data = self::MESSAGE_DB_NOT_SET;
        }
        return $data;
    }
}
