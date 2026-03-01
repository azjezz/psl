<?php

declare(strict_types=1);

namespace Psl\Terminal\Widget;

use Psl\Ansi\Color\Color;

/**
 * A value object representing a border configuration.
 *
 * Combines a border style with optional color and per-side control.
 *
 * @immutable
 */
final readonly class Border
{
    public function __construct(
        public BorderStyle $style = BorderStyle::Rounded,
        public null|Color $color = null,
        public bool $top = true,
        public bool $right = true,
        public bool $bottom = true,
        public bool $left = true,
    ) {}

    public static function rounded(null|Color $color = null): self
    {
        return new self(BorderStyle::Rounded, $color);
    }

    public static function plain(null|Color $color = null): self
    {
        return new self(BorderStyle::Plain, $color);
    }

    public static function double(null|Color $color = null): self
    {
        return new self(BorderStyle::Double, $color);
    }

    public static function thick(null|Color $color = null): self
    {
        return new self(BorderStyle::Thick, $color);
    }

    /**
     * @return array{string, string, string, string, string, string}
     *  [top-left, top-right, bottom-left, bottom-right, horizontal, vertical]
     */
    public function characters(): array
    {
        return $this->style->characters();
    }
}
