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
use DbIpGeo\Reader as DbIpReader;

class Client
{
    protected $ua = null;
    protected $ip = null;
    protected $db = null;
    protected $locale = null;
    protected $regexes = null;

    const SX_GEO             = 'sypex';
    const GEO_IP             = 'geoip';
    const DB_IP              = 'dbip';
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
        return $this->orderGeo();
    }

    protected function orderGeo()
    {
        $data = array();
        if(!isset($this->db['sorting'])) {
            $tmp = $this->db;
            $this->db['sorting'] = array();
            $this->db['sorting'][] = self::FIRST;
            foreach ($tmp as $key => $value) {
                if($key != self::FIRST) {
                    $this->db['sorting'][] = $key;
                }
            }
        }
        for ($i=0; $i < count($this->db['sorting']); $i++) {
            $index = $this->db['sorting'][$i];
            switch ($index) {
                case self::SX_GEO:
                    if($this->isArrayAndKeysExists(['country', 'region', 'city'], $data)) {
                        $counter = $this->countUnknown($data);
                        $check = $this->getSxGeo();
                        if(is_array($check)) {
                            if($counter > $this->countUnknown($check)) {
                                $data = $check;
                            }
                        }
                    } else {
                        $data = $this->getSxGeo();
                    }
                    break;
                case self::GEO_IP:
                    if($this->isArrayAndKeysExists(['country', 'region', 'city'], $data)) {
                        $counter = $this->countUnknown($data);
                        $check = $this->getGeoIp();
                        if(is_array($check)) {
                            if($counter > $this->countUnknown($check)) {
                                $data = $check;
                            }
                        }
                    } else {
                        $data = $this->getGeoIp();
                    }
                    break;
                case self::DB_IP:
                    if($this->isArrayAndKeysExists(['country', 'region', 'city'], $data)) {
                        $counter = $this->countUnknown($data);
                        $check = $this->getDbIpGeo();
                        if(is_array($check)) {
                            if($counter > $this->countUnknown($check)) {
                                $data = $check;
                            }
                        }
                    } else {
                        $data = $this->getDbIpGeo();
                    }
                    break;
                default:
                    $data = ['country' => 'UN', 'region' => 'Unknown', 'city' => 'Unknown'];
            }
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
                $data['region'] = $resp->mostSpecificSubdivision->name;
                $data['city'] = $city;

            } catch (\GeoIp2\Exception\AddressNotFoundException $e) {
                if((ip2long($this->ip) >= 167772160 && ip2long($this->ip) <= 184549375)
                    || (ip2long($this->ip) >= 2886729728 && ip2long($this->ip) <= 2887778303)
                    || (ip2long($this->ip) >= 3232235520 && ip2long($this->ip) <= 3232301055)) { //networks classes A,B,C
                    $data['country'] = 'LO';
                    $data['region'] = 'Local Network';
                    $data['city'] = 'Local Network';
                } elseif((ip2long($this->ip) >= 2130706432 && ip2long($this->ip) <= 2147483647)){
                    $data['country'] = 'LO';
                    $data['region'] = 'Loopback';
                    $data['city'] = 'Loopback';
                } else {
                    $data['country'] = 'UN';
                    $data['region'] = 'Unknown';
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
        if(isset($this->db[self::SX_GEO])) {
            $reader = new SxReader($this->db[self::SX_GEO], $this->locale);
            $data = $reader->getGeo($this->ip);
        } else {
            $data = self::MESSAGE_DB_NOT_SET;
        }
        return $data;
    }

    protected function getDbIpGeo()
    {
        if(isset($this->db[self::DB_IP])) {
            $reader = new DbIpReader($this->db[self::DB_IP]);
            $data = $reader->getGeo($this->ip);
        } else {
            $data = self::MESSAGE_DB_NOT_SET;
        }
        return $data;
    }

    protected function isArrayAndKeysExists(array $keys, $arr)
    {
        if(is_array($arr)) {
            return !array_diff_key(array_flip($keys), $arr);     
        } else {
            return false;
        }
       
    }

    protected function countUnknown(array $arr)
    {
        $counter = 0;
        foreach ($arr as $key => $value) {
            if($value == "UN" || $value == "Unknown") {
                $counter++;
            }
        }
       return $counter;
    }
}
