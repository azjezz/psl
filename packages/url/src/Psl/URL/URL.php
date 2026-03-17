<?php

declare(strict_types=1);

namespace Psl\URL;

use Psl\URI\Authority\Authority;
use Psl\URI\PathKind;
use Psl\URI\URI;
use Stringable;

/**
 * Represents a fully-qualified network URL with scheme and authority.
 *
 * A URL is a URI subset that always includes a scheme and an authority component,
 * making it suitable for use as a network locator.
 *
 * @link https://www.rfc-editor.org/rfc/rfc3986#section-3 RFC 3986 Section 3 - Syntax Components
 * @link https://url.spec.whatwg.org/ WHATWG URL Standard
 */
final readonly class URL implements Stringable
{
    /**
     * Create a new URL instance.
     *
     * @param non-empty-string $scheme The URI scheme, always lowercased.
     * @param Authority $authority The authority component containing host, port, and user info.
     * @param string $path The path component, empty string or starts with '/'.
     * @param null|string $query The query component, or null if absent.
     * @param null|string $fragment The fragment component, or null if absent.
     */
    public function __construct(
        public string $scheme,
        public Authority $authority,
        public string $path,
        public null|string $query,
        public null|string $fragment,
    ) {}

    /**
     * Convert this URL to a URI.
     *
     * @link https://www.rfc-editor.org/rfc/rfc3986#section-5.3 RFC 3986 Section 5.3 - Component Recomposition
     */
    public function toURI(): URI
    {
        $pathKind = $this->path === '' ? PathKind::None : PathKind::Absolute;

        return new URI($this->scheme, $this->authority, $this->path, $pathKind, $this->query, $this->fragment);
    }

    /**
     * Recompose the URL into its string representation.
     *
     * @link https://www.rfc-editor.org/rfc/rfc3986#section-5.3 RFC 3986 Section 5.3 - Component Recomposition
     *
     * @return non-empty-string
     */
    public function toString(): string
    {
        $result = $this->scheme . '://' . $this->authority->toString();
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
     * Return the string representation of this URL.
     *
     * @return non-empty-string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
