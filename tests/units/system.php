<?php
use PHPUnit\Framework\TestCase;
use ClientChecker\Client;

/**
 * @group App
 * @group System
 */
class SystemTest extends TestCase
{

    /**
     * @dataProvider system_Provider
     */
    public function test_System($ua, $os)
    {
        $client = new Client(
              $ua,
              '127.0.0.1',
              array(
                  'geoip' => './GeoLite2-City.mmdb',
                  'sypex' => './SxGeoCity.dat'
              )
          );
        $this->assertEquals($os, $client->getOs());
    }

    public function system_Provider()
    {
        $csvFile = file(DOCROOT."/tests/pretest/useragents.csv");
        $data = [];
        $data[] = array("", "Unknown");
        foreach ($csvFile as $line) {
            $tmp = str_getcsv($line, ";", "\"");
            $data[] = array($tmp[0], $tmp[2]);
        }
        return $data;
    }

    /**
     * @dataProvider mobile_Provider
     */
    public function test_IsMobile($ua, $os)
    {
        $client = new Client(
          $ua,
          '127.0.0.1',
          array(
            'geoip' => './GeoLite2-City.mmdb',
            'sypex' => './SxGeoCity.dat'
          )
        );
        $this->assertEquals($os, $client->isMobile());
    }

    public function mobile_Provider()
    {
        $csvFile = file(DOCROOT."/tests/pretest/useragents.csv");
        $data = [];
        $data[] = array("", false);
        foreach ($csvFile as $line) {
            $tmp = str_getcsv($line, ";", "\"");
            $value = (($tmp[3] == 'true') ? true : false);
            $data[] = array($tmp[0], $value);
        }
        return $data;
    }
}
