<?php

declare(strict_types=1);

namespace Psl\Terminal\Widget;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Str;

use function array_values;

/**
 * A styled text fragment.
 *
 * @immutable
 */
final readonly class Span
{
    /**
     * @param list<ControlSequenceIntroducer> $style
     */
    private function __construct(
        public string $content,
        public array $style,
    ) {}

    /**
     * Create a styled span.
     */
    public static function styled(string $content, ControlSequenceIntroducer ...$style): self
    {
        return new self($content, array_values($style));
    }

    /**
     * Create an unstyled span.
     */
    public static function raw(string $content): self
    {
        return new self($content, []);
    }

    /**
     * Create a new span with different content but the same styles.
     */
    public function withContent(string $content): self
    {
        return new self($content, $this->style);
    }

    /**
     * Returns the display width of this span.
     */
    public function width(): int
    {
        return Str\width($this->content);
    }
}
