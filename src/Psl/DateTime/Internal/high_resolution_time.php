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
 * @mago-expect lint:best-practices/no-else-clause
 * @mago-expect lint:strictness/no-shorthand-ternary
 */
function high_resolution_time(): array
{
    /**
     * @var null|list{int, int} $offset
     */
    static $offset = null;

    if (null === $offset) {
        $offset = hrtime() ?: null;

        Psl\invariant(null !== $offset, 'The system does not provide a monotonic timer.');

        $time = system_time();

        $offset = [
            $time[0] - $offset[0],
            $time[1] - $offset[1],
        ];
    }

    [$seconds_offset, $nanoseconds_offset] = $offset;
    $high_resolution_time = hrtime();
    if (false === $high_resolution_time) {
        throw new Psl\Exception\InvariantViolationException('The system does not provide a monotonic timer.');
    }

    [$seconds, $nanoseconds] = $high_resolution_time;

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
