<?php

declare(strict_types=1);

namespace Psl\Regex\Internal;

use Closure;
use Psl\Regex\Exception;

use function error_clear_last;
use function error_reporting;

/**
 * @template T
 *
 * @param non-empty-string $function
 * @param Closure(): T $closure
 *
 * @throws Exception\InvalidPatternException
 * @throws Exception\RuntimeException
 *
 * @return T
 *
 * @pure
 *
 * @internal
 */
function call_preg(string $function, Closure $closure): mixed
{
    error_clear_last();
    $previousLevel = error_reporting(0);
    try {
        $result = $closure();
    } finally {
        error_reporting($previousLevel);
    }

    $error = namespace\get_preg_error($function);
    // @codeCoverageIgnoreStart
    if (null !== $error) {
        if (null !== $error['pattern_message']) {
            throw new Exception\InvalidPatternException($error['pattern_message'], $error['code']);
        }

        throw new Exception\RuntimeException($error['message'], $error['code']);
    }

    // @codeCoverageIgnoreEnd

    return $result;
}
