<?php

declare(strict_types=1);

namespace Psl\Html;

use function strip_tags as php_strip_tags;

/**
 * Strip HTML and PHP tags from a string.
 *
 * @param list<string> $allowedTags tags which should not be stripped.
 *
 * @pure
 */
function strip_tags(string $html, array $allowedTags = []): string
{
    return php_strip_tags($html, $allowedTags);
}
