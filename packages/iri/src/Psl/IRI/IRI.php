<?php

declare(strict_types=1);

namespace Psl\IRI;

use Psl\IRI\Internal\HostConverter;
use Psl\IRI\Internal\UnicodeEncoder;
use Psl\URI\Authority\Authority;
use Psl\URI\PathKind;
use Psl\URI\URI;
use Stringable;

/**
 * Internationalized Resource Identifier per RFC 3987.
 *
 * An IRI extends the URI syntax to allow Unicode characters. It can be
 * converted to a standard URI via {@see IRI::toURI()}.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3987
 */
final readonly class IRI implements Stringable
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
     * Convert this IRI to a URI by percent-encoding Unicode and punycode-encoding the host.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3987#section-3.1
     *
     * @throws \Psl\Punycode\Exception\EncodingException If Punycode encoding fails for an internationalized host.
     * @throws Exception\InvalidIRIException If the host contains invalid IDNA labels.
     */
    public function toURI(): URI
    {
        $authority = null;
        $thisAuthority = $this->authority;
        if ($thisAuthority !== null) {
            $host = HostConverter::convertToAscii($thisAuthority->host);
            $userInfo = $thisAuthority->userInfo !== null
                ? UnicodeEncoder::encodeToURI($thisAuthority->userInfo)
                : null;
            $authority = new Authority($userInfo, $host, $thisAuthority->port);
        }

        $path = UnicodeEncoder::encodeToURI($this->path);
        $query = $this->query !== null ? UnicodeEncoder::encodeToURI($this->query) : null;
        $fragment = $this->fragment !== null ? UnicodeEncoder::encodeToURI($this->fragment) : null;

        return new URI($this->scheme, $authority, $path, $this->pathKind, $query, $fragment);
    }

    /**
     * Returns the IRI string preserving Unicode characters.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3987#section-2.1
     */
    public function toString(): string
    {
        $result = '';

        if ($this->scheme !== null) {
            $result = $this->scheme . ':';
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
     * @link https://datatracker.ietf.org/doc/html/rfc3987#section-2.1
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
