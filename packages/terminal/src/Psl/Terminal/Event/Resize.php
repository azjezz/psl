<?php

declare(strict_types=1);

namespace Psl\Terminal\Event;

/**
 * Represents a terminal resize event.
 *
 * @immutable
 *
 * @api
 */
final readonly class Resize
{
    /**
     * @param positive-int $width
     * @param positive-int $height
     */
    public function __construct(
        public int $width,
        public int $height,
    ) {}
}
