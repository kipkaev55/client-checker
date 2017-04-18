<?php
use PHPUnit\Framework\TestCase;
use ClientChecker\Client;

/**
 * @group App
 * @group IP
 */
class IpTest extends TestCase
{

    /**
     * @dataProvider ip_ProviderLocal
     */
    public function test_IpLocal($ip)
    {
        $client = new Client(
          '',
          $ip,
          array(
            'geoip' => DOCROOT.'/GeoLite2-City.mmdb',
            'sypex' => DOCROOT.'/SxGeoCity.dat'
          )
        );
        $this->assertEquals(array('country' => "LO", 'region' => "Local Network", 'city' => "Local Network"), $client->getIpData());
    }

    public function ip_ProviderLocal()
    {
        $data = [];
        $data[] = array("10.0.0.0");
        $data[] = array("10.0.0.255");
        $data[] = array("10.255.255.255");
        $data[] = array("172.16.0.0");
        $data[] = array("172.31.255.255");
        $data[] = array("192.168.0.0");
        $data[] = array("192.168.255.0");
        $data[] = array("192.168.255.255");
        return $data;
    }

    /**
     * @dataProvider ip_ProviderLoopback
     */
    public function test_IpLoopback($ip)
    {
        $client = new Client(
          '',
          $ip,
          array(
            'geoip' => DOCROOT.'/GeoLite2-City.mmdb',
            'sypex' => DOCROOT.'/SxGeoCity.dat'
          )
        );
        $this->assertEquals(array('country' => "LO", 'region' => "Loopback", 'city' => "Loopback"), $client->getIpData());
    }

    public function ip_ProviderLoopback()
    {
        $data = [];
        $data[] = array("127.0.0.0");
        $data[] = array("127.0.0.255");
        $data[] = array("127.0.255.255");
        return $data;
    }

    /**
     * @dataProvider ip_Provider
     */
    public function test_Geo($ip)
    {
        $client = new Client(
          '',
          $ip,
          array(
            'geoip' => DOCROOT.'/GeoLite2-City.mmdb',
            'sypex' => DOCROOT.'/SxGeoCity.dat'
          )
        );
        $data = $client->getIpData();
        $this->assertInternalType("array", $data);
        $this->assertArrayHasKey('country', $data);
        $this->assertArrayHasKey('region', $data);
        $this->assertArrayHasKey('city', $data);
    }
    public function ip_Provider()
    {
        $data = [];
        for ($i=0; $i < 100; $i++) {
          $data[] = array("".mt_rand(0,255).".".mt_rand(0,255).".".mt_rand(0,255).".".mt_rand(0,255));
        }
        return $data;
    }
}
