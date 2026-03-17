<?php

declare(strict_types=1);

namespace Psl\DateTime\Internal;

use function explode;
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
    $parts = explode(' ', $time);
    $seconds = (int) $parts[1];
    $nanoseconds = (int) ((float) $parts[0] * (float) NANOSECONDS_PER_SECOND);

    return [$seconds, $nanoseconds];
}
