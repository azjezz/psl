<?php

declare(strict_types=1);

namespace Psl\DateTime\Internal;

use Psl\Str;

use function microtime;

use const Psl\DateTime\NANOSECONDS_PER_SECOND;

/**
 * @return array{int, int}
 *
 * @internal
 *
 * @psalm-mutation-free
 *
 * @psalm-suppress ImpureFunctionCall - `microtime()` it is mutation-free, as it performs a read-only operation from the systems clock,
 * and does not alter anything.
 */
function system_time(): array
{
    $time = microtime();

    $parts = Str\split($time, ' ');
    $seconds = (int) $parts[1];
    $nanoseconds = (int) (((float) $parts[0]) * ((float) NANOSECONDS_PER_SECOND));

    return [$seconds, $nanoseconds];
}
