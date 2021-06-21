<?php

declare(strict_types=1);

namespace Psl\Asio;

use Amp;

/**
 * @template T
 *
 * @param (callable(): T) $callback
 */
function defer(callable $callback): void
{
    Amp\defer($callback);
}
