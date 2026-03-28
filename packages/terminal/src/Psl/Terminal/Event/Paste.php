<?php

declare(strict_types=1);

namespace Psl\Terminal\Event;

/**
 * Represents a bracketed paste event.
 *
 * @immutable
 *
 * @api
 */
final readonly class Paste
{
    public function __construct(
        public string $text,
    ) {}
}
