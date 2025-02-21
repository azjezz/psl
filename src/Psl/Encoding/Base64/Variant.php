<?php

declare(strict_types=1);

namespace Psl\Encoding\Base64;

use Psl\Default\DefaultInterface;

/**
 * Enumerates variants of Base64 encoding.
 */
enum Variant implements DefaultInterface
{
    /**
     * The standard Base64 encoding variant.
     *
     * Character set:
     *
     *  [A-Z]      [a-z]      [0-9]      +     /
     *  0x41-0x5a, 0x61-0x7a, 0x30-0x39, 0x2b, 0x2f
     *
     * This variant is typically used in MIME messages and XML data.
     */
    case Standard;

    /**
     * A URL and filename safe Base64 encoding variant.
     *
     * Character set:
     *
     * [A-Z]      [a-z]      [0-9]      -     _
     * 0x41-0x5a, 0x61-0x7a, 0x30-0x39, 0x2d, 0x5f
     *
     * This variant is URL and filename safe as it doesn't use characters that might
     * be modified by URL encoding or are invalid for filenames.
     */
    case UrlSafe;

    /**
     * A Base64 encoding variant with a character set that starts with "./", then follows
     * with [A-Z] [a-z] [0-9].
     *
     * Character set:
     *
     * ./         [A-Z]      [a-z]     [0-9]
     * 0x2e-0x2f, 0x41-0x5a, 0x61-0x7a, 0x30-0x39
     *
     * This variant might be used in specific systems where the starting characters have
     * a special meaning or use.
     */
    case DotSlash;

    /**
     * A Base64 encoding variant ordered.
     *
     * Character set:
     *
     * [.-9]      [A-Z]      [a-z]
     * 0x2e-0x39, 0x41-0x5a, 0x61-0x7a
     *
     * This variant presents an ordering where numbers and some punctuation come first,
     * potentially offering benefits in sorted lists or for systems where such an order
     * is required.
     */
    case DotSlashOrdered;

    /**
     * Provides the default variant for Base64 encoding.
     *
     * By default, this method returns the `Standard` variant, which is widely used across
     * various applications, including MIME messages and XML data. It represents a safe
     * and common choice for general-purpose encoding needs.
     *
     * @return static The `Standard` variant of Base64 encoding.
     */
    #[\Override]
    public static function default(): static
    {
        return self::Standard;
    }
}
