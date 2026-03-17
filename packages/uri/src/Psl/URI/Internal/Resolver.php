<?php

declare(strict_types=1);

namespace Psl\URI\Internal;

use Psl\URI\Authority\Authority;
use Psl\URI\PathKind;
use Psl\URI\URI;

use function strrpos;
use function substr;

/**
 * RFC 3986 Section 5.2.2 reference resolution.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3986#section-5.2
 *
 * @internal
 */
final class Resolver
{
    /**
     * Resolve a relative reference against a base URI per RFC 3986 Section 5.2.2.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3986#section-5.2.2
     */
    public static function resolve(URI $base, URI $reference): URI
    {
        if ($reference->scheme !== null) {
            $path = Normalizer::removeDotSegments($reference->path);
            return self::buildURI(
                $reference->scheme,
                $reference->authority,
                $path,
                $reference->query,
                $reference->fragment,
            );
        }

        if ($reference->authority !== null) {
            $path = Normalizer::removeDotSegments($reference->path);
            return self::buildURI($base->scheme, $reference->authority, $path, $reference->query, $reference->fragment);
        }

        if ($reference->path === '') {
            $query = $reference->query ?? $base->query;
            return self::buildURI($base->scheme, $base->authority, $base->path, $query, $reference->fragment);
        }

        if ($reference->path[0] === '/') {
            $path = Normalizer::removeDotSegments($reference->path);
        } else {
            $path = self::merge($base, $reference->path);
            $path = Normalizer::removeDotSegments($path);
        }

        return self::buildURI($base->scheme, $base->authority, $path, $reference->query, $reference->fragment);
    }

    /**
     * Merge a relative path with the base URI per RFC 3986 Section 5.2.3.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3986#section-5.2.3
     */
    private static function merge(URI $base, string $relativePath): string
    {
        if ($base->authority !== null && $base->path === '') {
            return '/' . $relativePath;
        }

        $lastSlash = strrpos($base->path, '/');
        if ($lastSlash !== false) {
            return substr($base->path, 0, $lastSlash + 1) . $relativePath;
        }

        return $relativePath;
    }

    /**
     * Build a URI from its components.
     *
     * @param null|non-empty-string $scheme
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3986#section-5.3
     */
    private static function buildURI(
        null|string $scheme,
        null|Authority $authority,
        string $path,
        null|string $query,
        null|string $fragment,
    ): URI {
        $pathKind = PathKind::None;
        if ($path !== '') {
            $pathKind = $path[0] === '/' ? PathKind::Absolute : PathKind::Rootless;
        }

        return new URI($scheme, $authority, $path, $pathKind, $query, $fragment);
    }
}
