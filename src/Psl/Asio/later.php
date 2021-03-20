<?php

declare(strict_types=1);

namespace Psl\Asio;

/**
 * @return Awaitable<void>
 */
function later(): Awaitable
{
    return async(static function (): void {
      // noop
    });
}
