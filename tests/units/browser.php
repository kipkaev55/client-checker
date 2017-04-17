<?php
use PHPUnit\Framework\TestCase;
use ClientChecker\Client;
/**
 * @group App
 * @group Browser
 */
class BrowserTest extends TestCase
{
    /**
     * @dataProvider browser_Provider
     */
    public function test_Browser($ua, $browser)
    {
        $client = new Client(
              $ua,
              '127.0.0.1',
              array(
                  'geoip' => './GeoLite2-City.mmdb',
                  'sypex' => './SxGeoCity.dat'
              )
          );
        $this->assertEquals($browser, $client->getBrowser());
    }

    public function browser_Provider()
    {
        $csvFile = file(DOCROOT."/tests/pretest/useragents.csv");
        $data = [];
        $data[] = array("", "Unknown");
        foreach ($csvFile as $line) {
            $tmp = str_getcsv($line, ";", "\"");
            $data[] = array($tmp[0], $tmp[1]);
        }
        return $data;
    }
}
