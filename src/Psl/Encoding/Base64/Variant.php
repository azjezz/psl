<?php

declare(strict_types=1);

namespace Psl\Encoding\Base64;

enum Variant
{
    /**
     * Base64 character set:
     *  [A-Z]      [a-z]      [0-9]      +     /
     *  0x41-0x5a, 0x61-0x7a, 0x30-0x39, 0x2b, 0x2f
     */
    case Default;
    /**
     * Base64url character set:
     * [A-Z]      [a-z]      [0-9]      -     _
     * 0x41-0x5a, 0x61-0x7a, 0x30-0x39, 0x2d, 0x5f
     */
    case UrlSafe;
    /**
     * Base64dotSlash character set:
     * ./         [A-Z]      [a-z]     [0-9]
     * 0x2e-0x2f, 0x41-0x5a, 0x61-0x7a, 0x30-0x39
     */
    case DotSlash;
    /**
     * Base64dotSlashOrdered character set:
     * [.-9]      [A-Z]      [a-z]
     * 0x2e-0x39, 0x41-0x5a, 0x61-0x7a
     */
    case DotSlashOrdered;
}
