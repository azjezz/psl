<?php

declare(strict_types=1);

namespace Psl\URI\Authority;

use Stringable;

/**
 * Represents the authority component of a URI per RFC 3986.
 *
 * The authority component has the form: [userinfo@]host[:port]
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3986#section-3.2
 */
final readonly class Authority implements Stringable
{
    /**
     * @param null|string $userInfo null = absent, "" = @ present but empty.
     * @param null|int<0, 65535> $port null = absent.
     */
    public function __construct(
        public null|string $userInfo,
        public HostInterface $host,
        public null|int $port,
    ) {}

    /**
     * Recompose the authority component per RFC 3986 Section 3.2.
     *
     * Format: [userinfo@]host[:port]
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3986#section-3.2
     */
    public function toString(): string
    {
        $result = '';
        if ($this->userInfo !== null) {
            $result .= $this->userInfo . '@';
        }

        $result .= $this->host->toString();

        if ($this->port !== null) {
            $result .= ':' . $this->port;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
