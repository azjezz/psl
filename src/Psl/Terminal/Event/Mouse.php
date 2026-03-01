<?php

declare(strict_types=1);

namespace Psl\Terminal\Event;

/**
 * Represents a mouse event.
 *
 * @immutable
 */
final readonly class Mouse
{
    public function __construct(
        public MouseKind $kind,
        public int $column,
        public int $row,
        public MouseButton $button = MouseButton::None,
        public MouseModifiers $modifiers = new MouseModifiers(),
    ) {}
}
