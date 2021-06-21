<?php

declare(strict_types=1);

namespace Psl\Asio;

use Amp;

/**
 * Get the system's high resolution time in milliseconds.
 */
function time(): int
{
    return Amp\getCurrentTime();
}
