<?php

declare(strict_types=1);

namespace Psl\URI;

use Psl\URI\Authority\Authority;
use Stringable;

/**
 * Represents a parsed and normalized URI per RFC 3986.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3986
 */
final readonly class URI implements Stringable
{
    /**
     * @param null|non-empty-string $scheme
     * @param null|string $query null = absent, "" = ? present but empty.
     * @param null|string $fragment null = absent, "" = # present but empty.
     */
    public function __construct(
        public null|string $scheme,
        public null|Authority $authority,
        public string $path,
        public PathKind $pathKind,
        public null|string $query,
        public null|string $fragment,
    ) {}

    /**
     * Recompose the URI per RFC 3986 Section 5.3.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3986#section-5.3
     */
    public function toString(): string
    {
        $result = '';

        if ($this->scheme !== null) {
            $result .= $this->scheme . ':';
        }

        if ($this->authority !== null) {
            $result .= '//' . $this->authority->toString();
        }

        $result .= $this->path;

        if ($this->query !== null) {
            $result .= '?' . $this->query;
        }

        if ($this->fragment !== null) {
            $result .= '#' . $this->fragment;
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
