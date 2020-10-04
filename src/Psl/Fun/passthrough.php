<?php

declare(strict_types=1);

namespace Psl\Fun;

/**
 * This method creates a callback that returns the value passed as argument.
 * It can e.g. be used as a success callback.
 *
 * @template T
 *
 * @psalm-return callable(T): T
 *
 * @psalm-pure
 */
function passthrough(): callable
{
    return static fn ($result) => $result;
}
