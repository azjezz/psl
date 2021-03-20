<?php

declare(strict_types=1);

namespace Psl\Asio;

use function hrtime;

/**
 * Get the system's high resolution time in milliseconds.
 */
function time(): int
{
    [$seconds, $nanoseconds] = hrtime(false);

    $result = 0;
    $result += $seconds * 1000;
    $result += (int)($nanoseconds / 1000000);

    return (int)$result;
}
