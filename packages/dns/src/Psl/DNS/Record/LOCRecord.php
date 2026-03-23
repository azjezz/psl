<?php

declare(strict_types=1);

namespace Psl\DNS\Record;

use Psl\DateTime\Duration;

/**
 * A LOC record specifying the geographic location of a domain name (RFC 1876).
 *
 * @api
 */
final class LOCRecord implements RecordInterface
{
    /**
     * {@inheritDoc}
     */
    public RecordType $kind {
        get => RecordType::LOC;
    }

    /**
     * The latitude in decimal degrees (positive = north, negative = south).
     */
    public float $latitude {
        get => ($this->latitudeRaw - 2_147_483_648) / 3_600_000.0;
    }

    /**
     * The longitude in decimal degrees (positive = east, negative = west).
     */
    public float $longitude {
        get => ($this->longitudeRaw - 2_147_483_648) / 3_600_000.0;
    }

    /**
     * The altitude in meters above the WGS 84 reference spheroid.
     */
    public float $altitude {
        get => ($this->altitudeRaw - 10_000_000) / 100.0;
    }

    /**
     * The diameter of the location sphere in meters.
     */
    public float $size {
        get => self::decodeLocSize($this->sizeRaw);
    }

    /**
     * The horizontal precision in meters.
     */
    public float $horizontalPrecision {
        get => self::decodeLocSize($this->horizontalPrecisionRaw);
    }

    /**
     * The vertical precision in meters.
     */
    public float $verticalPrecision {
        get => self::decodeLocSize($this->verticalPrecisionRaw);
    }

    /**
     * @param string $name The domain name this record belongs to.
     * @param Duration $duration The time-to-live for this record.
     * @param int $version The LOC record version (must be 0).
     * @param int $latitudeRaw The raw wire latitude value (unsigned 32-bit integer).
     * @param int $longitudeRaw The raw wire longitude value (unsigned 32-bit integer).
     * @param int $altitudeRaw The raw wire altitude value (unsigned 32-bit integer).
     * @param int $sizeRaw The raw wire size byte.
     * @param int $horizontalPrecisionRaw The raw wire horizontal precision byte.
     * @param int $verticalPrecisionRaw The raw wire vertical precision byte.
     */
    public function __construct(
        public readonly string $name,
        public readonly Duration $duration,
        public readonly int $version,
        public readonly int $latitudeRaw,
        public readonly int $longitudeRaw,
        public readonly int $altitudeRaw,
        public readonly int $sizeRaw,
        public readonly int $horizontalPrecisionRaw,
        public readonly int $verticalPrecisionRaw,
    ) {}

    /**
     * Decode a LOC size/precision byte into meters.
     *
     * The byte encodes a value in centimeters as mantissa * 10^exponent,
     * where mantissa is the upper nibble and exponent is the lower nibble.
     */
    private static function decodeLocSize(int $byte): float
    {
        $mantissa = ($byte >> 4) & 0x0F;
        $exponent = $byte & 0x0F;

        return ($mantissa * (10 ** $exponent)) / 100.0;
    }
}
