<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Internal;

use function array_pop;
use function explode;
use function implode;

/**
 * Merge a relative path with a base path per RFC 3986 Section 5.2.3.
 *
 * Removes the last segment of the base path (everything after the final "/")
 * and appends the relative path. If the base path is empty, the relative path
 * is prefixed with "/".
 *
 * @param string $basePath The base URL's path component.
 * @param string $relativePath The relative path to merge.
 *
 * @return string The merged path (not yet dot-segment resolved).
 *
 * @link https://www.rfc-editor.org/rfc/rfc3986#section-5.2.3
 *
 * @see resolve_url() Uses this function for relative path resolution.
 * @see remove_dot_segments() Should be applied to the result.
 *
 * @internal
 */
function merge_paths(string $basePath, string $relativePath): string
{
    if ($relativePath === '') {
        return $basePath;
    }

    if ($basePath === '') {
        return '/' . $relativePath;
    }

    $parts = explode('/', $basePath);
    array_pop($parts);
    $parts[] = $relativePath;

    return implode('/', $parts);
}
