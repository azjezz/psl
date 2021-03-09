<?php

declare(strict_types=1);

namespace Psl\Html;

use function strip_tags as php_strip_tags;

/**
 * Strip HTML and PHP tags from a string.
 *
 * @param list<string> $allowed_tags tags which should not be stripped.
 *
 * @pure
 */
function strip_tags(string $html, array $allowed_tags = []): string
{
    /**
     * @psalm-suppress InvalidArgument
     *
     * @link https://github.com/vimeo/psalm/issues/5330
     */
    return php_strip_tags($html, $allowed_tags);
}
