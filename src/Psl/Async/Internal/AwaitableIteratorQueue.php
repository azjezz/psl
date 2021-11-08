<?php

declare(strict_types=1);

namespace Psl\Async\Internal;

use Psl\Async\Awaitable;
use Revolt\EventLoop\Suspension;

/**
 * The following class was derived from code of Amphp.
 *
 * https://github.com/amphp/amp/blob/ac89b9e2ee04228e064e424056a08590b0cdc7b3/lib/Future.php
 *
 * Code subject to the MIT license (https://github.com/amphp/amp/blob/ac89b9e2ee04228e064e424056a08590b0cdc7b3/LICENSE).
 *
 * Copyright (c) 2015-2021 Amphp ( https://amphp.org )
 *
 * @template Tk
 * @template Tv
 *
 * @internal
 *
 * @codeCoverageIgnore
 */
final class AwaitableIteratorQueue
{
    /**
     * @var array<int, array{0: Tk, 1: Awaitable<Tv>}>
     */
    public array $items = [];

    /**
     * @var array<string, State<Tv>>
     */
    public array $pending = [];

    public ?Suspension $suspension = null;
}
