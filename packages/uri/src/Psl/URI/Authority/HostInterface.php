<?php

declare(strict_types=1);

namespace Psl\URI\Authority;

use Stringable;

/**
 * Represents a URI host component per RFC 3986.
 *
 * A host may be an IP address ({@see IPHost}) or a registered name ({@see RegisteredNameHost}).
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3986#section-3.2.2
 *
 * @psalm-inheritors IPHost|RegisteredNameHost
 */
interface HostInterface extends Stringable
{
    /**
     * Return the canonical string representation of the host.
     *
     * @return non-empty-string
     */
    public function toString(): string;

    /**
     * Return the canonical string representation of the host.
     *
     * @return non-empty-string
     */
    public function __toString(): string;
}
