<?php

namespace Camcima;

class GeoHashTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param float $latitude
     * @param float $longitude
     * @param string $hash
     *
     * @dataProvider geohashProvider
     */
    public function testEncode($latitude, $longitude, $precision, $hash)
    {
        $geoHash = new GeoHash();
        $geoHash->setLatitude($latitude);
        $geoHash->setLongitude($longitude);
        if ($precision) {
            $geoHash->setPrecision($precision);
        }

        $this->assertEquals($hash, $geoHash->getHash());
    }

    /**
     * @param float $latitude
     * @param float $longitude
     * @param string $hash
     *
     * @dataProvider geohashProvider
     */
    public function testDecode($latitude, $longitude, $precision, $hash)
    {
        $geoHash = new GeoHash();
        $geoHash->setHash($hash);

        $this->assertEquals($latitude, $geoHash->getLatitude());
        $this->assertEquals($longitude, $geoHash->getLongitude());
    }

    /**
     * All data come from http://geohash.org
     */
    public function geohashProvider()
    {
        return [
            [31.283131, 121.500831, null, 'wtw3uyfjqw61'],
            [31.28, 121.500831, null, 'wtw3uy65nwdh'],
            [31.283131, 121.500, null, 'wtw3uyct7nq3'],
            [31.283131, 121.500, null, 'wtw3uyct7nq3'],
            [-25.382708, -49.265506, null, '6gkzwgjzn820'],
            [-25.383, -49.266, null, '6gkzwgjt'],
            [-25.427, -49.315, null, '6gkzmg1u'],
            [45.37, -121.7, null, 'c216ne'],
            [26.08461, -80.38893, .000001, 'dhwu6sw9f5t'],
            [52.524451, 13.387871, null, 'u33db9uc0524'],
        ];
    }
}
