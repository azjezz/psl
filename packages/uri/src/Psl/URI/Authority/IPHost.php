<?php

declare(strict_types=1);

namespace Psl\URI\Authority;

use Psl\IP;
use Psl\IP\Address;

/**
 * Represents an IP-address host in a URI per RFC 3986.
 *
 * IPv6 addresses are rendered in bracketed notation per RFC 3986 Section 3.2.2.
 * IPv6 addresses use RFC 5952 canonical form via {@see Address::toString()}.
 * Zone identifiers are supported per RFC 6874.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3986#section-3.2.2
 * @link https://datatracker.ietf.org/doc/html/rfc5952
 * @link https://datatracker.ietf.org/doc/html/rfc6874
 */
final readonly class IPHost implements HostInterface
{
    /**
     * @param null|string $zone RFC 6874 zone ID, null for most addresses.
     */
    public function __construct(
        public Address $address,
        public null|string $zone = null,
    ) {}

    /**
     * Returns the canonical string representation of the IP host.
     *
     * IPv4: raw address (e.g. "192.168.1.1")
     * IPv6: bracketed address (e.g. "[::1]")
     * IPv6 with zone: bracketed with percent-encoded zone (e.g. "[fe80::1%25eth0]")
     *
     * @return non-empty-string
     */
    public function toString(): string
    {
        if ($this->address->family === IP\Family::V4) {
            return $this->address->toString();
        }

        if ($this->zone !== null) {
            return '[' . $this->address->toString() . '%25' . $this->zone . ']';
        }

        return '[' . $this->address->toString() . ']';
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
