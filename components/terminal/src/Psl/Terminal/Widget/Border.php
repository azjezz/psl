<?php

declare(strict_types=1);

namespace Psl\Terminal\Widget;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Vec;

/**
 * A value object representing a border configuration.
 *
 * Combines a border style with optional styling and per-side control.
 *
 * @immutable
 */
final readonly class Border
{
    /**
     * @param list<ControlSequenceIntroducer> $style
     */
    public function __construct(
        public BorderStyle $borderStyle = BorderStyle::Rounded,
        public array $style = [],
        public bool $top = true,
        public bool $right = true,
        public bool $bottom = true,
        public bool $left = true,
    ) {}

    public static function rounded(ControlSequenceIntroducer ...$style): self
    {
        return new self(BorderStyle::Rounded, Vec\values($style));
    }

    public static function plain(ControlSequenceIntroducer ...$style): self
    {
        return new self(BorderStyle::Plain, Vec\values($style));
    }

    public static function double(ControlSequenceIntroducer ...$style): self
    {
        return new self(BorderStyle::Double, Vec\values($style));
    }

    public static function thick(ControlSequenceIntroducer ...$style): self
    {
        return new self(BorderStyle::Thick, Vec\values($style));
    }

    /**
     * @return array{string, string, string, string, string, string}
     *  [top-left, top-right, bottom-left, bottom-right, horizontal, vertical]
     */
    public function characters(): array
    {
        return $this->borderStyle->characters();
    }
}
