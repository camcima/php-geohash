<?php
declare(strict_types=1);

namespace Camcima;

class GeoHash
{
    /**
     * @var string
     */
    private static $characterTable = '0123456789bcdefghjkmnpqrstuvwxyz';

    /**
     * @var string
     */
    private $hash;

    /**
     * @var float
     */
    private $latitude;

    /**
     * @var float
     */
    private $longitude;

    /**
     * @var float
     */
    private $precision;

    /**
     * @param string $hash
     *
     * @return GeoHash
     */
    public static function fromHash(string $hash): GeoHash
    {
        $geoHash = new GeoHash();
        $geoHash->setHash($hash);

        return $geoHash;
    }

    /**
     * @param float $latitude
     * @param float $longitude
     * @param float $precision
     *
     * @return GeoHash
     */
    public static function fromCoordinates(float $latitude, float $longitude, float $precision = 0): GeoHash
    {
        $geoHash = new GeoHash();
        $geoHash->setLatitude($latitude);
        $geoHash->setLongitude($longitude);
        if ($precision) {
            $geoHash->setPrecision($precision);
        }

        return $geoHash;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        if (!$this->hash) {
            if ($this->latitude === null) {
                throw new \RuntimeException('Latitude is required');
            }

            if ($this->longitude === null) {
                throw new \RuntimeException('Longitude is required');
            }

            $this->hash = $this->createHash();
        }

        return $this->hash;
    }

    /**
     * Set a hash, this will clear any latitude/longitude or precision set.
     *
     * @param string $hash
     *
     * @return GeoHash
     */
    public function setHash(string $hash): GeoHash
    {
        $this->hash = strtolower($hash);
        $this->parseHash();

        return $this;
    }

    /**
     * Get the latitude.
     *
     * @return float
     */
    public function getLatitude(): float
    {
        return (float) $this->latitude;
    }

    /**
     * Set a latitude, this will clear any hash.
     *
     * @param float $latitude
     *
     * @return GeoHash
     */
    public function setLatitude(float $latitude): GeoHash
    {
        $this->hash = '';
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get the longitude.
     *
     * @return float
     */
    public function getLongitude(): float
    {
        return (float) $this->longitude;
    }

    /**
     * Set a latitude, this will clear any hash.
     *
     * @param float $longitude
     *
     * @return GeoHash
     */
    public function setLongitude(float $longitude): GeoHash
    {
        $this->hash = '';
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Gets the precision.
     *
     * @return float
     */
    public function getPrecision(): float
    {
        return (float) $this->precision;
    }

    /**
     * Set a precision, clears any hash.
     *
     * @param float $precision
     *
     * @return GeoHash
     */
    public function setPrecision(float $precision): GeoHash
    {
        $this->hash = '';
        $this->precision = $precision;

        return $this;
    }

    /**
     * Return the hash, obviously, to print out.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->getHash();
    }

    private function resetCoordinates()
    {
        $this->latitude = null;
        $this->longitude = null;
        $this->precision = null;
    }

    /**
     * @return string
     */
    private function createHash(): string
    {
        $longitude = $this->longitude;
        $latitude = $this->latitude;

        if (isset($this->precision)) {
            $precision = $this->precision;
        } else {
            $latitudePrecision = strlen((string) $latitude) - strpos((string) $latitude, '.');
            $longitudePrecision = strlen((string) $longitude) - strpos((string) $longitude, '.');
            $precision = pow(10, -max($latitudePrecision - 1, $longitudePrecision - 1, 0)) / 2;
            $this->precision = $precision;
        }

        $minLatitude = (float) -90;
        $maxLatitude = (float) 90;
        $minLongitude = (float) -180;
        $maxLongitude = (float) 180;
        $latitudeError = (float) 90;
        $longitudeError = (float) 180;
        $error = (float) 180;

        $i = 0;
        $hash = '';
        while ($error >= $precision) {
            $digitValue = 0;
            for ($bit = 4; $bit >= 0; --$bit) {
                if ((1 & $bit) == (1 & $i)) { // even char, even bit OR odd char, odd bit...a lng
                    $next = ($minLongitude + $maxLongitude) / 2;

                    if ($longitude > $next) {
                        $digitValue |= pow(2, $bit);
                        $minLongitude = $next;
                    } else {
                        $maxLongitude = $next;
                    }

                    $longitudeError /= 2;
                } else { // odd char, even bit OR even char, odd bit...a lat
                    $next = ($minLatitude + $maxLatitude) / 2;

                    if ($latitude > $next) {
                        $digitValue |= pow(2, $bit);
                        $minLatitude = $next;
                    } else {
                        $maxLatitude = $next;
                    }

                    $latitudeError /= 2;
                }
            }
            $hash .= self::$characterTable[$digitValue];
            $error = min($latitudeError, $longitudeError);
            $i++;
        }

        return $hash;
    }

    private function parseHash()
    {
        $hash = $this->hash;

        $this->resetCoordinates();

        $minLatitude = -90;
        $maxLatitude = 90;
        $minLongitude = -180;
        $maxLongitude = 180;
        $latitudeError = 90;
        $longitudeError = 180;

        for ($i = 0; $i < strlen($hash); $i++) {
            $characterValue = strpos(self::$characterTable, $hash[$i]);

            if (1 & $i) {
                if (16 & $characterValue) {
                    $minLatitude = ($minLatitude + $maxLatitude) / 2;
                } else {
                    $maxLatitude = ($minLatitude + $maxLatitude) / 2;
                }

                if (8 & $characterValue) {
                    $minLongitude = ($minLongitude + $maxLongitude) / 2;
                } else {
                    $maxLongitude = ($minLongitude + $maxLongitude) / 2;
                }

                if (4 & $characterValue) {
                    $minLatitude = ($minLatitude + $maxLatitude) / 2;
                } else {
                    $maxLatitude = ($minLatitude + $maxLatitude) / 2;
                }

                if (2 & $characterValue) {
                    $minLongitude = ($minLongitude + $maxLongitude) / 2;
                } else {
                    $maxLongitude = ($minLongitude + $maxLongitude) / 2;
                }

                if (1 & $characterValue) {
                    $minLatitude = ($minLatitude + $maxLatitude) / 2;
                } else {
                    $maxLatitude = ($minLatitude + $maxLatitude) / 2;
                }

                $latitudeError /= 8;
                $longitudeError /= 4;
            } else {
                if (16 & $characterValue) {
                    $minLongitude = ($minLongitude + $maxLongitude) / 2;
                } else {
                    $maxLongitude = ($minLongitude + $maxLongitude) / 2;
                }

                if (8 & $characterValue) {
                    $minLatitude = ($minLatitude + $maxLatitude) / 2;
                } else {
                    $maxLatitude = ($minLatitude + $maxLatitude) / 2;
                }

                if (4 & $characterValue) {
                    $minLongitude = ($minLongitude + $maxLongitude) / 2;
                } else {
                    $maxLongitude = ($minLongitude + $maxLongitude) / 2;
                }

                if (2 & $characterValue) {
                    $minLatitude = ($minLatitude + $maxLatitude) / 2;
                } else {
                    $maxLatitude = ($minLatitude + $maxLatitude) / 2;
                }

                if (1 & $characterValue) {
                    $minLongitude = ($minLongitude + $maxLongitude) / 2;
                } else {
                    $maxLongitude = ($minLongitude + $maxLongitude) / 2;
                }

                $latitudeError /= 4;
                $longitudeError /= 8;
            }
        }

        $this->latitude = (float) round(($minLatitude + $maxLatitude) / 2, (int) max(1, -round(log10($latitudeError))) - 1);
        $this->longitude = (float) round(($minLongitude + $maxLongitude) / 2, (int) max(1, -round(log10($longitudeError))) - 1);
        $this->precision = (float) max($latitudeError, $longitudeError);
    }
}
