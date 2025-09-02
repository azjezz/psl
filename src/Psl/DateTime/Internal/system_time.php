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
 */
function system_time(): array
{
    $time = microtime();

    /** @var list{numeric-string, numeric-string} */
    $parts = Str\split($time, ' ');
    $seconds = (int) $parts[1];
    $nanoseconds = (int) ((float) $parts[0] * (float) NANOSECONDS_PER_SECOND);

    return [$seconds, $nanoseconds];
}
