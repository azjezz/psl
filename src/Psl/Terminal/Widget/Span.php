<?php

declare(strict_types=1);

namespace Psl\Terminal\Widget;

use Psl\Ansi\Color\Color;
use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Str;

/**
 * A styled text fragment.
 *
 * @immutable
 */
final readonly class Span
{
    /**
     * @param list<ControlSequenceIntroducer> $modifiers
     */
    private function __construct(
        public string $content,
        public null|Color $foreground,
        public null|Color $background,
        public array $modifiers,
    ) {}

    /**
     * Create a styled span.
     *
     * @param list<ControlSequenceIntroducer> $modifiers
     */
    public static function styled(
        string $content,
        null|Color $foreground = null,
        null|Color $background = null,
        null|ControlSequenceIntroducer $style = null,
        array $modifiers = [],
    ): self {
        if ($style !== null) {
            $modifiers[] = $style;
        }

        return new self($content, $foreground, $background, $modifiers);
    }

    /**
     * Create an unstyled span.
     */
    public static function raw(string $content): self
    {
        return new self($content, null, null, []);
    }

    /**
     * Create a new span with different content but the same styles.
     */
    public function withContent(string $content): self
    {
        return new self($content, $this->foreground, $this->background, $this->modifiers);
    }

    /**
     * Returns the display width of this span.
     */
    public function width(): int
    {
        return Str\width($this->content);
    }
}
