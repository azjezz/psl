<?php

declare(strict_types=1);

namespace Psl\URI;

/**
 * Discriminates URI path types per RFC 3986.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3986#section-3.3
 */
enum PathKind
{
    /**
     * Path starts with "/"
     */
    case Absolute;

    /**
     * Path does not start with "/" and is non-empty
     */
    case Rootless;

    /**
     * Path is empty string
     */
    case None;
}
