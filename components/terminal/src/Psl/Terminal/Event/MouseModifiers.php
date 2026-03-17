<?php

declare(strict_types=1);

namespace Psl\Terminal\Event;

/**
 * Mouse modifier keys extracted from SGR mouse protocol.
 *
 * @immutable
 */
final readonly class MouseModifiers
{
    public const int SHIFT = 4;
    public const int ALT = 8;
    public const int CTRL = 16;

    public function __construct(
        public int $value = 0,
    ) {}

    public function shift(): bool
    {
        return ($this->value & self::SHIFT) !== 0;
    }

    public function alt(): bool
    {
        return ($this->value & self::ALT) !== 0;
    }

    public function ctrl(): bool
    {
        return ($this->value & self::CTRL) !== 0;
    }
}
