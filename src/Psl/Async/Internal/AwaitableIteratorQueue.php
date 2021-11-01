<?php

declare(strict_types=1);

namespace Psl\Async\Internal;

use Psl\Async\Awaitable;
use Revolt\EventLoop\Suspension;

/**
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
