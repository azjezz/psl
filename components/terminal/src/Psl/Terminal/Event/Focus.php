<?php

declare(strict_types=1);

namespace Psl\Terminal\Event;

/**
 * Represents a terminal focus/blur event.
 *
 * Emitted when the terminal window gains or loses focus (requires focus tracking mode).
 *
 * @immutable
 */
final readonly class Focus
{
    public function __construct(
        public bool $focused,
    ) {}
}
