<?php

declare(strict_types=1);

namespace Psl\Fun;

use Exception;

/**
 * This method creates a callback that throws the exception passed as argument.
 * It can e.g. be used as a failure callback.
 *
 * @psalm-return (callable(Exception): no-return)
 *
 * @psalm-pure
 */
function rethrow(): callable
{
    return static function (Exception $exception): void {
        throw $exception;
    };
}
