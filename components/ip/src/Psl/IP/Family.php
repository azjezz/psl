<?php

declare(strict_types=1);

namespace Psl\IP;

use Psl\IP\Exception\InvalidArgumentException;

/**
 * IP address family, with values representing the byte size of the address.
 */
enum Family: int
{
    case V4 = 4;
    case V6 = 16;

    /**
     * Create from an IANA address family number (RFC 7871).
     *
     * @throws InvalidArgumentException If the IANA family number is not recognized.
     *
     * @psalm-assert 1|2 $value
     */
    public static function fromIanaFamily(int $value): self
    {
        return match ($value) {
            1 => self::V4,
            2 => self::V6,
            default => throw new InvalidArgumentException(
                'Expected IANA address family 1 (IPv4) or 2 (IPv6), got ' . $value . '.',
            ),
        };
    }

    /**
     * Get the IANA address family number (1 for IPv4, 2 for IPv6).
     *
     * @return 1|2
     */
    public function ianaFamily(): int
    {
        return match ($this) {
            self::V4 => 1,
            self::V6 => 2,
        };
    }
}
