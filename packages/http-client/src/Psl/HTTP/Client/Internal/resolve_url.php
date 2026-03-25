<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Internal;

use Psl\URL;

use function str_contains;
use function str_starts_with;
use function strpos;
use function substr;

/**
 * Resolve a URI reference against a base URL per RFC 3986 Section 5.2.2.
 *
 * Handles four forms of URI reference:
 * - Absolute URI (contains "://"): parsed independently, base URL ignored.
 * - Protocol-relative (starts with "//"): inherits scheme from base URL, new authority and path.
 * - Absolute path (starts with "/"): path is resolved with dot-segment removal.
 * - Relative path (e.g., "path", "../path"): merged with the base path then
 *   dot-segments are removed.
 *
 * Query strings and fragments on the reference are preserved. If the reference
 * has no query, the base URL's query is used only when the reference path is empty.
 *
 * @param non-empty-string $reference The URI reference to resolve.
 * @param URL\URL $baseUrl The base URL to resolve against.
 *
 * @return URL\URL The resolved absolute URL.
 *
 * @throws URL\Exception\InvalidURLException If an absolute reference cannot be parsed.
 *
 * @link https://www.rfc-editor.org/rfc/rfc3986#section-5.2.2
 *
 * @see merge_paths() Merges a relative path with a base path.
 * @see remove_dot_segments() Removes "." and ".." segments from a path.
 *
 * @internal
 */
function resolve_url(string $reference, URL\URL $baseUrl): URL\URL
{
    if (str_contains($reference, '://')) {
        return URL\parse($reference);
    }

    // Protocol-relative reference (//authority/path): inherit scheme from base URL.
    if (str_starts_with($reference, '//')) {
        return URL\parse($baseUrl->scheme . ':' . $reference);
    }

    $fragment = null;
    if (str_contains($reference, '#')) {
        /** @var int<0, max> $fPos */
        $fPos = strpos($reference, '#');
        $fragment = substr($reference, $fPos + 1);
        $reference = substr($reference, 0, $fPos);
    }

    $query = null;
    if (str_contains($reference, '?')) {
        /** @var int<0, max> $qPos */
        $qPos = strpos($reference, '?');
        $query = substr($reference, $qPos + 1);
        $reference = substr($reference, 0, $qPos);
    }

    $path = $reference;

    if ($path === '') {
        return new URL\URL($baseUrl->scheme, $baseUrl->authority, $baseUrl->path, $query ?? $baseUrl->query, $fragment);
    }

    if (str_starts_with($path, '/')) {
        $resolvedPath = namespace\remove_dot_segments($path);
    } else {
        $resolvedPath = namespace\remove_dot_segments(namespace\merge_paths($baseUrl->path, $path));
    }

    return new URL\URL($baseUrl->scheme, $baseUrl->authority, $resolvedPath, $query, $fragment);
}
