<?php

declare(strict_types=1);

namespace Psl\Terminal\Event;

/**
 * Represents a terminal resize event.
 *
 * @immutable
 */
final readonly class Resize
{
    public function __construct(
        public int $width,
        public int $height,
    ) {}
}
