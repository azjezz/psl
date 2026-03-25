<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Internal;

use function strlen;
use function substr;

/**
 * Consume up to $maxBytes from a buffer string, returning what was consumed
 * and leaving the remainder in the buffer.
 *
 * Used by body handle implementations ({@see H2\ResponseBodyHandle},
 * {@see H1\ChunkedBodyHandle}) to serve partial reads from an internal buffer
 * without copying the entire buffer on every read.
 *
 * @param string $buffer The buffer to consume from (modified in-place).
 * @param null|positive-int $maxBytes Maximum bytes to consume, or null for all.
 *
 * @return string The consumed bytes (may be '' if the buffer is empty).
 *
 * @internal
 */
function consume_buffer(string &$buffer, null|int $maxBytes): string
{
    if ($buffer === '') {
        return '';
    }

    if ($maxBytes === null || strlen($buffer) <= $maxBytes) {
        $data = $buffer;
        $buffer = '';
        return $data;
    }

    /** @var non-negative-int $maxBytes */
    $data = substr($buffer, 0, $maxBytes);
    $buffer = substr($buffer, $maxBytes);
    return $data;
}
