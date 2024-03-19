<?php

declare(strict_types=1);

namespace Psl\DateTime;

use JsonSerializable;

/**
 * Represents the geographical location of a timezone.
 *
 * @immutable
 */
final class TimezoneLocation implements JsonSerializable
{
    /**
     * @param non-empty-string $countryCode The ISO 3166-1 alpha-2 country code representing the country of the timezone.
     * @param float $latitude The latitude of the timezone's location in degrees.
     * @param float $longitude The longitude of the timezone's location in degrees.
     */
    public function __construct(
        public readonly string $countryCode,
        public readonly float $latitude,
        public readonly float $longitude,
    ) {
    }

    /**
     * Serializes the timezone location to an associative array format for JSON encoding.
     *
     * The resulting array includes the country code, latitude, and longitude of the timezone,
     * keyed by 'country_code', 'latitude', and 'longitude' respectively. This structure
     * facilitates straightforward conversion to JSON for API responses or data storage.
     *
     * @return array{
     *     country_code: string,
     *     latitude: float,
     *     longitude: float,
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'country_code' => $this->countryCode,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
