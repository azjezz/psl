<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Internal;

use function array_pop;
use function explode;
use function implode;
use function str_starts_with;

/**
 * Remove "." and ".." dot segments from a path per RFC 3986 Section 5.2.4.
 *
 * Iterates over path segments and resolves "." (current directory) and ".."
 * (parent directory) references, producing a normalized absolute or relative path.
 * Preserves the leading "/" if the input path starts with one.
 *
 * @param string $path The path to normalize.
 *
 * @return string The path with dot segments removed.
 *
 * @link https://www.rfc-editor.org/rfc/rfc3986#section-5.2.4
 *
 * @see resolve_url() Uses this function after merging paths.
 *
 * @internal
 */
function remove_dot_segments(string $path): string
{
    $output = [];
    $segments = explode('/', $path);

    foreach ($segments as $segment) {
        if ($segment === '.') {
            continue;
        }

        if ($segment === '..') {
            if ($output !== []) {
                array_pop($output);
            }

            continue;
        }

        $output[] = $segment;
    }

    $result = implode('/', $output);

    if (str_starts_with($path, '/') && !str_starts_with($result, '/')) {
        return '/' . $result;
    }

    return $result;
}
