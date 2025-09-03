<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl\Regex;

use function preg_quote;
use function preg_split;

/**
 * Returns the '$haystack' string with all occurrences of `$needle` replaced by
 * `$replacement` (case-insensitive).
 *
 * @pure
 *
 * @throws Exception\InvalidArgumentException if $needle is not a valid UTF-8 string.
 */
function replace_ci(string $haystack, string $needle, string $replacement, Encoding $encoding = Encoding::Utf8): string
{
    if ('' === $haystack || '' === $needle || null === namespace\search_ci($haystack, $needle, 0, $encoding)) {
        return $haystack;
    }

    try {
        $pieces = Regex\Internal\call_preg(
            'preg_split',
            /**
             * @return list<non-empty-string>
             */
            static function () use ($haystack, $needle): array {
                $result = preg_split('{' . preg_quote($needle, '/') . '}iu', $haystack, -1);
                if (false === $result) {
                    $result = [];
                }

                return $result;
            },
        );
    } catch (Regex\Exception\RuntimeException|Regex\Exception\InvalidPatternException $error) {
        throw new Exception\InvalidArgumentException($error->getMessage(), previous: $error);
    }

    return namespace\join($pieces, $replacement);
}
