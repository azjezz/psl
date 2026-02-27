<?php

declare(strict_types=1);

namespace Psl\TLS;

use Psl\Default\DefaultInterface;

/**
 * Represents the minimum or maximum TLS protocol version to use.
 */
enum Version: int implements DefaultInterface
{
    case Tls10 = 0;
    case Tls11 = 1;
    case Tls12 = 2;
    case Tls13 = 3;

    /**
     * @pure
     */
    public static function default(): static
    {
        return self::Tls13;
    }
}
