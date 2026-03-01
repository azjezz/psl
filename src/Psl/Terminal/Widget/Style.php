<?php

declare(strict_types=1);

namespace Psl\Terminal\Widget;

use Psl\Ansi\Color\Color;
use Psl\Ansi\ControlSequenceIntroducer;

/**
 * A reusable style value object holding foreground, background, and modifiers.
 *
 * @immutable
 */
final readonly class Style
{
    /**
     * @param list<ControlSequenceIntroducer> $modifiers
     */
    public function __construct(
        public null|Color $foreground = null,
        public null|Color $background = null,
        public array $modifiers = [],
    ) {}
}
