<?php

declare(strict_types=1);

namespace Psl\Regex\Internal;

use Psl\Str;

use const PREG_BACKTRACK_LIMIT_ERROR;
use const PREG_BAD_UTF8_ERROR;
use const PREG_BAD_UTF8_OFFSET_ERROR;
use const PREG_INTERNAL_ERROR;
use const PREG_JIT_STACKLIMIT_ERROR;
use const PREG_NO_ERROR;
use const PREG_RECURSION_LIMIT_ERROR;

/**
 * @pure
 *
 * @return null|array{message: string, code: int, pattern_message: null|string}
 *
 * @internal
 */
function get_preg_error(string $function): ?array
{
    /** @psalm-suppress ImpureFunctionCall */
    $code = preg_last_error();
    if ($code === PREG_NO_ERROR) {
        return null;
    }

    $messages = [
        PREG_INTERNAL_ERROR => 'Internal error',
        PREG_BAD_UTF8_ERROR => 'Malformed UTF-8 characters, possibly incorrectly encoded',
        PREG_BAD_UTF8_OFFSET_ERROR => 'The offset did not correspond to the beginning of a valid UTF-8 code point',
        PREG_BACKTRACK_LIMIT_ERROR => 'Backtrack limit exhausted',
        PREG_RECURSION_LIMIT_ERROR => 'Recursion limit exhausted',
        PREG_JIT_STACKLIMIT_ERROR => 'JIT stack limit exhausted',
    ];

    $message = $messages[$code] ?? 'Unknown error';
    $result = ['message' => $message, 'code' => $code, 'pattern_message' => null];

    /** @psalm-suppress ImpureFunctionCall */
    $error = error_get_last();
    /** @psalm-suppress MissingThrowsDocblock */
    if (null !== $error && Str\starts_with($error['message'], $function)) {
        /** @psalm-suppress MissingThrowsDocblock */
        $result['pattern_message'] = Str\strip_prefix($error['message'], Str\format('%s(): ', $function));
    }

    return $result;
}
