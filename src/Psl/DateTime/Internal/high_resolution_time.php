<?php

declare(strict_types=1);

namespace Psl\DateTime\Internal;

use Psl;

use function hrtime;

use const Psl\DateTime\NANOSECONDS_PER_SECOND;

/**
 * @throws Psl\Exception\InvariantViolationException
 *
 * @return array{int, int}
 *
 * @internal
 *
 * @psalm-mutation-free
 *
 * @psalm-suppress ImpureStaticVariable - We ignore the internal mutation, as is simply a static initializer.
 * @psalm-suppress ImpureFunctionCall - `hrtime()` it is mutation-free, as it performs a read-only operation from the systems clock,
 *  and does not alter anything.
 *
 * @mago-ignore best-practices/no-boolean-literal-comparison
 * @mago-ignore best-practices/no-else-clause
 */
function high_resolution_time(): array
{
    /**
     * @var null|array{int, int} $offset
     */
    static $offset = null;

    if ($offset === null) {
        $offset = hrtime();

        /** @psalm-suppress RedundantCondition - This is not redundant, hrtime can return false. */
        Psl\invariant(false !== $offset, 'The system does not provide a monotonic timer.');

        $time = system_time();

        $offset = [
            $time[0] - $offset[0],
            $time[1] - $offset[1],
        ];
    }

    [$seconds_offset, $nanoseconds_offset] = $offset;
    [$seconds, $nanoseconds] = hrtime();

    $nanoseconds_adjusted = $nanoseconds + $nanoseconds_offset;
    if ($nanoseconds_adjusted >= NANOSECONDS_PER_SECOND) {
        ++$seconds;
        $nanoseconds_adjusted -= NANOSECONDS_PER_SECOND;
    } elseif ($nanoseconds_adjusted < 0) {
        --$seconds;
        $nanoseconds_adjusted += NANOSECONDS_PER_SECOND;
    }

    $seconds += $seconds_offset;
    $nanoseconds = $nanoseconds_adjusted;

    return [$seconds, $nanoseconds];
}
