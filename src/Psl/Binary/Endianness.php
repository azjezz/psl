<?php

declare(strict_types=1);

namespace Psl\Binary;

use Override;
use Psl\Default\DefaultInterface;

/**
 * Byte order for multi-byte binary encoding and decoding.
 */
enum Endianness implements DefaultInterface
{
    /**
     * Big-endian (network byte order). Most significant byte first.
     */
    case Big;

    /**
     * Little-endian. Least significant byte first.
     */
    case Little;

    /**
     * @pure
     */
    #[Override]
    public static function default(): static
    {
        return self::Big;
    }
}
